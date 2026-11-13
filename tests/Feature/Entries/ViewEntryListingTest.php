<?php

namespace Tests\Feature\Entries;

use Facades\Statamic\Fields\BlueprintRepository;
use Facades\Tests\Factories\EntryFactory;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Entries\Collection;
use Statamic\Facades\Blueprint;
use Statamic\Facades\User;
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
    public function it_only_lists_the_ids_and_types_of_replicator_sets_and_grid_rows()
    {
        $collection = tap(Collection::make('test'))->save();

        $blueprint = Blueprint::makeFromFields([
            'sets' => ['type' => 'replicator', 'listable' => true, 'sets' => ['main' => ['sets' => [
                'text' => ['fields' => [['handle' => 'words', 'field' => ['type' => 'text']]]],
            ]]]],
            'rows' => ['type' => 'grid', 'listable' => true, 'fields' => [
                ['handle' => 'words', 'field' => ['type' => 'text']],
            ]],
        ])->setHandle('test');

        BlueprintRepository::partialMock();
        BlueprintRepository::shouldReceive('in')->with('collections/test')->andReturn(collect([$blueprint]));

        EntryFactory::collection($collection)->slug('one')->data([
            'sets' => [
                ['id' => 'set-a', 'type' => 'text', 'words' => 'Lots of words'],
                ['id' => 'set-b', 'type' => 'text', 'words' => 'More words', 'enabled' => false],
            ],
            'rows' => [
                ['id' => 'row-a', 'words' => 'Lots of words'],
                ['id' => 'row-b', 'words' => 'More words'],
                ['id' => 'row-c', 'words' => 'Even more words'],
            ],
        ])->create();

        $response = $this
            ->actingAs(tap(User::make()->makeSuper())->save())
            ->get(cp_route('collections.entries.index', ['collection' => 'test', 'columns' => 'title,sets,rows']))
            ->assertOk();

        $entry = $response->json('data.0');

        $this->assertSame([
            ['id' => 'set-a', 'type' => 'text'],
            ['id' => 'set-b', 'type' => 'text', 'enabled' => false],
        ], $entry['sets']);

        $this->assertSame([['id' => 'row-a'], ['id' => 'row-b'], ['id' => 'row-c']], $entry['rows']);
    }
}
