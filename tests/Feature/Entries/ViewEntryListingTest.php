<?php

namespace Tests\Feature\Entries;

use Facades\Tests\Factories\EntryFactory;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Entries\Collection;
use Statamic\Facades\User;
use Statamic\Fields\Blueprint;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ViewEntryListingTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_shows_entries_index()
    {
        $this->setTestRoles(['test' => [
            'access cp',
            'view test entries',
        ]]);

        $user = tap(User::make()->assignRole('test'))->save();

        $collection = tap(Collection::make('test'))->save();

        foreach (['one', 'two', 'three'] as $handle) {
            EntryFactory::collection($collection)
                ->slug($handle)
                ->create();
        }

        $response = $this
            ->actingAs($user)
            ->get(cp_route('collections.entries.index', ['collection' => 'test']))
            ->assertOk();

        $entries = collect($response->getData()->data);

        $this->assertEquals(['one', 'two', 'three'], $entries->pluck('slug')->all());
    }

    #[Test]
    public function it_shows_only_entries_in_index_for_sites_user_can_access()
    {
        $this->setSites([
            'en' => ['url' => '/', 'locale' => 'en_US', 'name' => 'English'],
            'fr' => ['url' => '/', 'locale' => 'fr_FR', 'name' => 'French'],
            'de' => ['url' => '/', 'locale' => 'de_DE', 'name' => 'German'],
        ]);

        $collection = tap(Collection::make('test'))->save();

        foreach (['en', 'fr', 'de'] as $locale) {
            foreach (['one', 'two', 'three'] as $handle) {
                EntryFactory::collection($collection)
                    ->slug("{$locale}-{$handle}")
                    ->locale($locale)
                    ->create();
            }
        }

        $this->setTestRoles(['test' => [
            'access cp',
            'view test entries',
            'access en site',
            'access de site',
        ]]);

        $user = tap(User::make()->assignRole('test'))->save();

        $response = $this
            ->actingAs($user)
            ->get(cp_route('collections.entries.index', ['collection' => 'test']))
            ->assertOk();

        $entries = collect($response->getData()->data);

        $expected = [
            'en-one',
            'en-two',
            'en-three',
            'de-one',
            'de-two',
            'de-three',
        ];

        $this->assertEquals($expected, $entries->pluck('slug')->all());
    }

    #[Test]
    public function it_shows_only_entries_in_index_the_user_can_access()
    {
        $this->setTestRole('view-own-entries', [
            'access cp',
            'view test entries',
        ]);

        $this->setTestRole('view-other-authors-entries', [
            'access cp',
            'view test entries',
            'view other authors test entries',
        ]);

        $userOne = tap(User::make()->assignRole('view-own-entries'))->save();
        $userTwo = tap(User::make()->assignRole('view-other-authors-entries'))->save();

        Blueprint::make('with-author')
            ->setNamespace('collections/test')
            ->ensureField('author', [])
            ->save();

        Blueprint::make('without-author')
            ->setNamespace('collections/test')
            ->save();

        $collection = tap(Collection::make('test'))->save();

        EntryFactory::collection($collection)
            ->slug('entry-user-one')
            ->data(['blueprint' => 'with-author', 'author' => $userOne->id()])
            ->create();

        EntryFactory::collection($collection)
            ->slug('entry-user-two')
            ->data(['blueprint' => 'with-author', 'author' => $userTwo->id()])
            ->create();

        EntryFactory::collection($collection)
            ->slug('entry-with-multiple-authors')
            ->data(['blueprint' => 'with-author', 'author' => [$userOne->id(), $userTwo->id()]])
            ->create();

        EntryFactory::collection($collection)
            ->slug('entry-without-author')
            ->data(['blueprint' => 'without-author'])
            ->create();

        $responseUserOne = $this
            ->actingAs($userOne)
            ->get(cp_route('collections.entries.index', ['collection' => 'test']))
            ->assertOk();

        $entries = collect($responseUserOne->getData()->data);

        $expected = [
            'entry-user-one',
            'entry-with-multiple-authors',
            'entry-without-author',
        ];

        $this->assertEquals($expected, $entries->pluck('slug')->all());

        $responseUserTwo = $this
            ->actingAs($userTwo)
            ->get(cp_route('collections.entries.index', ['collection' => 'test']))
            ->assertOk();

        $entries = collect($responseUserTwo->getData()->data);

        $expected = [
            'entry-user-one',
            'entry-user-two',
            'entry-with-multiple-authors',
            'entry-without-author',
        ];

        $this->assertEquals($expected, $entries->pluck('slug')->all());
    }
}
