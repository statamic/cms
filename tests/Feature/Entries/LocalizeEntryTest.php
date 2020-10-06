<?php

namespace Tests\Feature\Entries;

use Facades\Tests\Factories\EntryFactory;
use Statamic\Facades\Collection;
use Statamic\Facades\Site;
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

        Site::setConfig(['sites' => [
            'en' => ['url' => 'http://localhost/', 'locale' => 'en'],
            'fr' => ['url' => 'http://localhost/fr/', 'locale' => 'fr'],
        ]]);
    }

    /** @test */
    public function it_localizes_an_entry()
    {
        $user = $this->user();
        $entry = EntryFactory::collection('blog')->slug('test')->create();
        $this->assertNull($entry->in('fr'));

        $response = $this
            ->actingAs($user)
            ->localize($entry, ['site' => 'fr'])
            ->assertOk();

        $localized = $entry->fresh()->in('fr');
        $this->assertNotNull($localized);
        $this->assertEquals($user, $localized->lastModifiedBy());
        $response->assertJson(['handle' => 'fr', 'url' => $localized->editUrl()]);
    }

    /** @test */
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

    /** @test */
    public function it_adds_an_entry_to_the_structure_tree_if_its_nested()
    {
        $collection = tap(Collection::make('pages')->sites(['en', 'fr']))->save();
        $enHome = EntryFactory::collection($collection)->slug('home')->locale('en')->id('home')->create();
        $fnHome = EntryFactory::collection($collection)->slug('home')->locale('fr')->id('fr-home')->origin('home')->create();
        $enAbout = EntryFactory::collection($collection)->slug('about')->locale('en')->id('about')->create();
        $frAbout = EntryFactory::collection($collection)->slug('about')->locale('fr')->id('fr-about')->origin('about')->create();
        $enTeam = EntryFactory::collection($collection)->slug('team')->locale('en')->id('team')->create();
        $this->assertNull($enTeam->in('fr'));
        $collection->structureContents(['root' => true, 'tree' => [
            'en' => [
                ['entry' => 'home'],
                ['entry' => 'about', 'children' => [
                    ['entry' => 'team'],
                ]],
            ],
            'fr' => [
                ['entry' => 'fr-home'],
                ['entry' => 'fr-about'],
            ],
        ]])->save();

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

    /** @test */
    public function it_adds_an_entry_to_the_end_of_the_structure_tree_if_the_parent_is_the_root()
    {
        $collection = tap(Collection::make('pages')->sites(['en', 'fr']))->save();
        $enHome = EntryFactory::collection($collection)->slug('home')->locale('en')->id('home')->create();
        $fnHome = EntryFactory::collection($collection)->slug('home')->locale('fr')->id('fr-home')->origin('home')->create();
        $enAbout = EntryFactory::collection($collection)->slug('about')->locale('en')->id('about')->create();
        $frAbout = EntryFactory::collection($collection)->slug('about')->locale('fr')->id('fr-about')->origin('about')->create();
        $enTeam = EntryFactory::collection($collection)->slug('team')->locale('en')->id('team')->create();
        $this->assertNull($enTeam->in('fr'));
        $collection->structureContents(['root' => true, 'tree' => [
            'en' => [
                ['entry' => 'home'],
                ['entry' => 'about'],
                ['entry' => 'team'],
            ],
            'fr' => [
                ['entry' => 'fr-home'],
                ['entry' => 'fr-about'],
            ],
        ]])->save();

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

    /** @test */
    public function it_adds_an_entry_to_the_end_of_the_structure_tree_if_the_parent_doesnt_exist_in_that_site()
    {
        $collection = tap(Collection::make('pages')->sites(['en', 'fr']))->save();
        $enHome = EntryFactory::collection($collection)->slug('home')->locale('en')->id('home')->create();
        $fnHome = EntryFactory::collection($collection)->slug('home')->locale('fr')->id('fr-home')->origin('home')->create();
        $enAbout = EntryFactory::collection($collection)->slug('about')->locale('en')->id('about')->create();
        $enTeam = EntryFactory::collection($collection)->slug('team')->locale('en')->id('team')->create();
        $this->assertNull($enTeam->in('fr'));
        $collection->structureContents(['root' => true, 'tree' => [
            'en' => [
                ['entry' => 'home'],
                ['entry' => 'about', 'children' => [
                    ['entry' => 'team'],
                ]],
            ],
            'fr' => [
                ['entry' => 'fr-home'],
            ],
        ]])->save();

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
        $this->setTestRoles(['test' => ['access cp']]);

        return User::make()->assignRole('test')->save();
    }
}
