<?php

namespace Tests\Feature\Entries;

use Facades\Tests\Factories\EntryFactory;
use Illuminate\Contracts\Cache\LockProvider;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class LocalizeEntryTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        // When using the array driver, the same entry object is always returned which breaks
        // subsequent localization lookups.
        config(['cache.default' => 'file']);
        \Illuminate\Support\Facades\Cache::clear();

        $this->setSites([
            'en' => ['url' => 'http://localhost/', 'locale' => 'en'],
            'fr' => ['url' => 'http://localhost/fr/', 'locale' => 'fr'],
        ]);
    }

    #[Test]
    public function it_localizes_an_entry()
    {
        $user = $this->user();
        $entry = EntryFactory::collection(tap(Collection::make('blog')->dated(true)->revisionsEnabled(false))->save())->slug('test')->date('2013-08-24')->create();
        $this->assertNull($entry->in('fr'));

        $response = $this
            ->actingAs($user)
            ->localize($entry, ['site' => 'fr'])
            ->assertOk();

        $localized = $entry->fresh()->in('fr');
        $this->assertNotNull($localized);
        $this->assertEquals($user, $localized->lastModifiedBy());
        $this->assertEquals('2013-08-24', $localized->date()->format('Y-m-d'));
        $response->assertJson(['handle' => 'fr', 'url' => $localized->editUrl()]);

        $this->assertCount(0, $entry->in('fr')->revisions());
    }

    #[Test]
    public function it_returns_the_existing_localization_instead_of_creating_another()
    {
        $entry = EntryFactory::collection(tap(Collection::make('blog')->revisionsEnabled(false))->save())->id('en-test')->slug('test')->create();
        $existing = EntryFactory::collection('blog')->id('fr-test')->locale('fr')->origin('en-test')->slug('test')->create();

        $this
            ->actingAs($this->user())
            ->localize($entry, ['site' => 'fr'])
            ->assertOk()
            ->assertJson(['handle' => 'fr', 'url' => $existing->editUrl()]);

        $this->assertCount(1, \Statamic\Facades\Entry::query()->where('origin', 'en-test')->where('site', 'fr')->get());
    }

    #[Test]
    public function it_returns_an_existing_localization_from_elsewhere_in_the_origin_chain()
    {
        $this->setSites([
            'en' => ['url' => 'http://localhost/', 'locale' => 'en'],
            'de' => ['url' => 'http://localhost/de/', 'locale' => 'de'],
            'fr' => ['url' => 'http://localhost/fr/', 'locale' => 'fr'],
        ]);

        $en = EntryFactory::collection(tap(Collection::make('blog')->revisionsEnabled(false))->save())->id('en-test')->slug('test')->create();
        $de = EntryFactory::collection('blog')->id('de-test')->locale('de')->origin('en-test')->slug('test')->create();

        $first = $this->actingAs($this->user())->localize($de, ['site' => 'fr'])->assertOk();
        $second = $this->actingAs($this->user())->localize($en, ['site' => 'fr'])->assertOk();

        $this->assertSame($first->json(), $second->json());
        $this->assertCount(1, \Statamic\Facades\Entry::query()->where('collection', 'blog')->where('site', 'fr')->get());
    }

    #[Test]
    public function it_localizes_an_entry_when_the_cache_store_does_not_support_locks()
    {
        Cache::extend('nolock', fn ($app) => Cache::repository(new class(Cache::store('file')->getStore()) implements Store
        {
            public function __construct(private Store $store)
            {
            }

            public function get($key)
            {
                return $this->store->get($key);
            }

            public function many(array $keys)
            {
                return $this->store->many($keys);
            }

            public function put($key, $value, $seconds)
            {
                return $this->store->put($key, $value, $seconds);
            }

            public function putMany(array $values, $seconds)
            {
                return $this->store->putMany($values, $seconds);
            }

            public function increment($key, $value = 1)
            {
                return $this->store->increment($key, $value);
            }

            public function decrement($key, $value = 1)
            {
                return $this->store->decrement($key, $value);
            }

            public function forever($key, $value)
            {
                return $this->store->forever($key, $value);
            }

            public function touch($key, $seconds)
            {
                return $this->store->touch($key, $seconds);
            }

            public function forget($key)
            {
                return $this->store->forget($key);
            }

            public function flush()
            {
                return $this->store->flush();
            }

            public function getPrefix()
            {
                return $this->store->getPrefix();
            }
        }));

        config(['cache.stores.nolock' => ['driver' => 'nolock'], 'cache.default' => 'nolock']);
        $this->assertNotInstanceOf(LockProvider::class, Cache::getStore());

        $entry = EntryFactory::collection(tap(Collection::make('blog')->revisionsEnabled(false))->save())->slug('test')->create();

        $this
            ->actingAs($this->user())
            ->localize($entry, ['site' => 'fr'])
            ->assertOk();

        $this->assertNotNull($entry->fresh()->in('fr'));
    }

    #[Test]
    public function site_is_required()
    {
        $entry = EntryFactory::collection('blog')->slug('test')->create();

        $this
            ->actingAs($this->user())
            ->from('/original')
            ->localize($entry, ['site' => ''])
            ->assertRedirect('/original')
            ->assertSessionHasErrors('site');
    }

    #[Test]
    public function cant_localize_entry_without_edit_permissions()
    {
        $user = $this->user();
        $this->setTestRoles(['test' => ['access cp', 'access en site', 'access fr site', 'view blog entries']]);

        $entry = EntryFactory::collection(tap(Collection::make('blog')->revisionsEnabled(false))->save())->slug('test')->create();
        $this->assertNull($entry->in('fr'));

        $this
            ->actingAs($user->fresh())
            ->localize($entry, ['site' => 'fr'])
            ->assertRedirect();

        $localized = $entry->fresh()->in('fr');
        $this->assertNull($localized);
    }

    #[Test]
    public function it_adds_an_entry_to_the_structure_tree_if_its_nested()
    {
        $collection = tap(Collection::make('pages')->sites(['en', 'fr']))->save();
        $enHome = EntryFactory::collection($collection)->slug('home')->locale('en')->id('home')->create();
        $fnHome = EntryFactory::collection($collection)->slug('home')->locale('fr')->id('fr-home')->origin('home')->create();
        $enAbout = EntryFactory::collection($collection)->slug('about')->locale('en')->id('about')->create();
        $frAbout = EntryFactory::collection($collection)->slug('about')->locale('fr')->id('fr-about')->origin('about')->create();
        $enTeam = EntryFactory::collection($collection)->slug('team')->locale('en')->id('team')->create();
        $this->assertNull($enTeam->in('fr'));
        $collection->structureContents(['root' => true])->save();
        $collection->structure()->in('en')->tree(
            [
                ['entry' => 'home'],
                ['entry' => 'about', 'children' => [
                    ['entry' => 'team'],
                ]],
            ]
        )->save();
        $collection->structure()->in('fr')->tree(
            [
                ['entry' => 'fr-home'],
                ['entry' => 'fr-about'],
            ]
        )->save();

        $this->assertEquals([
            ['entry' => 'fr-home'],
            ['entry' => 'fr-about'],
        ], Collection::findByHandle('pages')->structure()->in('fr')->tree());

        $this
            ->actingAs($this->user())
            ->localize($enTeam, ['site' => 'fr'])
            ->assertOk();

        $frTeam = $enTeam->fresh()->in('fr');
        $this->assertNotNull($frTeam);

        $this->assertEquals([
            ['entry' => 'fr-home'],
            ['entry' => 'fr-about', 'children' => [
                ['entry' => $frTeam->id()],
            ]],
        ], Collection::findByHandle('pages')->structure()->in('fr')->tree());
    }

    #[Test]
    public function it_adds_an_entry_to_the_end_of_the_structure_tree_if_the_parent_is_the_root()
    {
        $collection = tap(Collection::make('pages')->sites(['en', 'fr']))->save();
        $enHome = EntryFactory::collection($collection)->slug('home')->locale('en')->id('home')->create();
        $fnHome = EntryFactory::collection($collection)->slug('home')->locale('fr')->id('fr-home')->origin('home')->create();
        $enAbout = EntryFactory::collection($collection)->slug('about')->locale('en')->id('about')->create();
        $frAbout = EntryFactory::collection($collection)->slug('about')->locale('fr')->id('fr-about')->origin('about')->create();
        $enTeam = EntryFactory::collection($collection)->slug('team')->locale('en')->id('team')->create();
        $this->assertNull($enTeam->in('fr'));
        $collection->structureContents(['root' => true])->save();
        $collection->structure()->in('en')->tree(
            [
                ['entry' => 'home'],
                ['entry' => 'about'],
                ['entry' => 'team'],
            ]
        )->save();
        $collection->structure()->in('fr')->tree(
            [
                ['entry' => 'fr-home'],
                ['entry' => 'fr-about'],
            ]
        )->save();

        $this->assertEquals([
            ['entry' => 'fr-home'],
            ['entry' => 'fr-about'],
        ], Collection::findByHandle('pages')->structure()->in('fr')->tree());

        $this
            ->actingAs($this->user())
            ->localize($enTeam, ['site' => 'fr'])
            ->assertOk();

        $frTeam = $enTeam->fresh()->in('fr');
        $this->assertNotNull($frTeam);

        $this->assertEquals([
            ['entry' => 'fr-home'],
            ['entry' => 'fr-about'],
            ['entry' => $frTeam->id()],
        ], Collection::findByHandle('pages')->structure()->in('fr')->tree());
    }

    #[Test]
    public function it_adds_an_entry_to_the_end_of_the_structure_tree_if_the_parent_doesnt_exist_in_that_site()
    {
        $collection = tap(Collection::make('pages')->sites(['en', 'fr']))->save();
        $enHome = EntryFactory::collection($collection)->slug('home')->locale('en')->id('home')->create();
        $fnHome = EntryFactory::collection($collection)->slug('home')->locale('fr')->id('fr-home')->origin('home')->create();
        $enAbout = EntryFactory::collection($collection)->slug('about')->locale('en')->id('about')->create();
        $enTeam = EntryFactory::collection($collection)->slug('team')->locale('en')->id('team')->create();
        $this->assertNull($enTeam->in('fr'));
        $collection->structureContents(['root' => true])->save();
        $collection->structure()->in('en')->tree(
            [
                ['entry' => 'home'],
                ['entry' => 'about', 'children' => [
                    ['entry' => 'team'],
                ]],
            ]
        )->save();
        $collection->structure()->in('fr')->tree(
            [
                ['entry' => 'fr-home'],
            ]
        )->save();

        $this->assertEquals([
            ['entry' => 'fr-home'],
        ], Collection::findByHandle('pages')->structure()->in('fr')->tree());

        $this
            ->actingAs($this->user())
            ->localize($enTeam, ['site' => 'fr'])
            ->assertOk();

        $frTeam = $enTeam->fresh()->in('fr');
        $this->assertNotNull($frTeam);

        $this->assertEquals([
            ['entry' => 'fr-home'],
            ['entry' => $frTeam->id()],
        ], Collection::findByHandle('pages')->structure()->in('fr')->tree());
    }

    #[Test]
    public function it_localizes_an_entry_with_revisions()
    {
        config(['statamic.revisions.enabled' => true]);

        $user = $this->user();
        $entry = EntryFactory::collection(tap(Collection::make('blog')->revisionsEnabled(true))->save())->slug('test')->create();
        $this->assertNull($entry->in('fr'));

        $response = $this
            ->actingAs($user)
            ->localize($entry, ['site' => 'fr'])
            ->assertOk();

        $localized = $entry->fresh()->in('fr');
        $this->assertNotNull($localized);
        $this->assertEquals($user, $localized->lastModifiedBy());
        $response->assertJson(['handle' => 'fr', 'url' => $localized->editUrl()]);

        $this->assertCount(1, $entry->in('fr')->revisions());
    }

    private function localize($entry, $params = [])
    {
        $url = cp_route('collections.entries.localize', [
            'collection' => $entry->collectionHandle(),
            'entry' => $entry->id(),
            'slug' => $entry->slug(),
        ]);

        return $this->post($url, $params);
    }

    private function user()
    {
        $this->setTestRoles(['test' => [
            'access cp',
            'access en site',
            'access fr site',
            'access de site',
            'view blog entries',
            'edit blog entries',
            'view pages entries',
            'edit pages entries',
        ]]);

        return User::make()->assignRole('test')->save();
    }
}
