<?php

namespace Tests\Feature\Collections;

use Facades\Tests\Factories\EntryFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class UpdateCollectionTreeTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    #[Test]
    #[DataProvider('collectionTreeDataProvider')]
    public function it_updates_the_tree($collectionHandle)
    {
        $this->withoutExceptionHandling();
        $user = tap(User::make()->makeSuper())->save();
        $collection = tap(Collection::make($collectionHandle)->routes('{parent_uri}/{slug}'))->save();
        EntryFactory::id('e1')->collection($collection)->slug('a')->create();
        EntryFactory::id('e2')->collection($collection)->slug('b')->create();
        EntryFactory::id('e3')->collection($collection)->slug('c')->create();
        EntryFactory::id('e4')->collection($collection)->slug('d')->create();
        EntryFactory::id('e5')->collection($collection)->slug('e')->create();
        $collection->structureContents(['foo' => 'bar'])->save();
        $collection->structure()->in('en')->tree([
            ['entry' => 'e1'],
            ['entry' => 'e2'],
            ['entry' => 'e3'],
            ['entry' => 'e4'],
            ['entry' => 'e5'],
        ])->save();

        $this
            ->actingAs($user)
            ->update($collection, ['pages' => [
                ['id' => 'e3', 'children' => [
                    ['id' => 'e5', 'children' => [
                        ['id' => 'e4', 'children' => []],
                    ]],
                ]],
                ['id' => 'e1', 'children' => []],
                ['id' => 'e2', 'children' => []],
            ]])
            ->assertOk();

        $this->assertEquals([
            ['entry' => 'e3', 'children' => [
                ['entry' => 'e5', 'children' => [
                    ['entry' => 'e4'],
                ]],
            ]],
            ['entry' => 'e1'],
            ['entry' => 'e2'],
        ], $collection->structure()->in('en')->tree());
    }

    public static function collectionTreeDataProvider()
    {
        return [
            'arbitrary handle' => ['pages'],
            'handle of collection' => ['collection'],
            'handle ending with collection' => ['foo_collection'],
            'handle starting with collection' => ['collection_foo'],
            'handle with collection inside' => ['foo_collection_foo'],
        ];
    }

    #[Test]
    public function it_doesnt_update_the_tree_if_theres_a_duplicate_uri_when_expecting_root()
    {
        $this->duplicateUriTest(true);
    }

    #[Test]
    public function it_doesnt_update_the_tree_if_theres_a_duplicate_uri_when_not_expecting_root()
    {
        $this->duplicateUriTest(false);
    }

    private function duplicateUriTest($expectsRoot)
    {
        $user = tap(User::make()->makeSuper())->save();
        $collection = tap(Collection::make('test')->routes('{parent_uri}/{slug}'))->save();
        EntryFactory::id('e1')->collection($collection)->slug('alfa')->create();
        EntryFactory::id('e2')->collection($collection)->slug('bravo')->create();
        EntryFactory::id('e3')->collection($collection)->slug('bravo')->create();
        EntryFactory::id('e4')->collection($collection)->slug('charlie')->create();
        EntryFactory::id('e5')->collection($collection)->slug('delta')->create();
        $collection->structureContents(['foo' => 'bar', 'root' => $expectsRoot])->save();
        $tree = $collection->structure()->in('en');
        $tree->tree([
            ['entry' => 'e1'],
            ['entry' => 'e3', 'children' => [
                ['entry' => 'e2'],
            ]],
            ['entry' => 'e4'],
            ['entry' => 'e5'],
        ])->save();

        $this
            ->actingAs($user)
            ->update($collection, ['pages' => [
                ['id' => 'e1', 'children' => []],
                ['id' => 'e2', 'children' => []],
                ['id' => 'e3', 'children' => []],
                ['id' => 'e4', 'children' => []],
                ['id' => 'e5', 'children' => []],
            ]])
            ->assertStatus(422);

        $this->assertEquals([
            ['entry' => 'e1'],
            ['entry' => 'e3', 'children' => [
                ['entry' => 'e2'],
            ]],
            ['entry' => 'e4'],
            ['entry' => 'e5'],
        ], $collection->structure()->in('en')->tree());
    }

    #[Test]
    public function it_deletes_entries_scheduled_for_deletion()
    {
        $user = tap(User::make()->makeSuper())->save();
        $collection = tap(Collection::make('test'))->save();
        EntryFactory::id('e1')->collection($collection)->create();
        EntryFactory::id('e2')->collection($collection)->create();
        EntryFactory::id('e3')->collection($collection)->create();
        $collection->structureContents(['tree' => []])->save();
        $this->assertCount(3, Entry::all());

        $this
            ->actingAs($user)
            ->update($collection, ['deletedEntries' => ['e1', 'e3']])
            ->assertOk();

        $this->assertCount(1, Entry::all());
    }

    #[Test]
    public function it_doesnt_delete_entries_if_theres_a_duplicate_uri_validation_error()
    {
        $user = tap(User::make()->makeSuper())->save();
        $collection = tap(Collection::make('test')->routes('{parent_uri}/{slug}'))->save();
        EntryFactory::id('e1')->collection($collection)->create();
        EntryFactory::id('e2')->collection($collection)->create();
        EntryFactory::id('e3')->collection($collection)->create();
        EntryFactory::id('e4')->collection($collection)->create();
        $collection->structureContents(['root' => false])->save();
        $tree = $collection->structure()->in('en');
        $tree->tree([
            ['entry' => 'e1'],
            ['entry' => 'e3', 'children' => [
                ['entry' => 'e2'],
            ]],
            ['entry' => 'e4'],
        ])->save();
        $this->assertCount(4, Entry::all());

        $this
            ->actingAs($user)
            ->update($collection, [
                'deletedEntries' => ['e1'],
                'pages' => [
                    ['id' => 'e1', 'children' => []],
                    ['id' => 'e2', 'children' => []],
                    ['id' => 'e3', 'children' => []],
                ],
            ])
            ->assertStatus(422);

        $this->assertCount(4, Entry::all());
        $this->assertEquals([
            ['entry' => 'e1'],
            ['entry' => 'e3', 'children' => [
                ['entry' => 'e2'],
            ]],
            ['entry' => 'e4'],
        ], $collection->structure()->in('en')->tree());
    }

    #[Test]
    public function it_denies_access_if_you_dont_have_permission_to_reorder()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $collection = tap(Collection::make('test')->structureContents(['tree' => []]))->save();

        $this
            ->actingAs($user)
            ->update($collection, ['site' => 'en', 'pages' => []])
            ->assertForbidden();
    }

    public function update($collection, $payload = [])
    {
        $validParams = [
            'site' => 'en',
            'pages' => [],
            'deletedEntries' => [],
        ];

        return $this->patchJson(
            cp_route('collections.tree.update', $collection->handle()),
            array_merge($validParams, $payload)
        );
    }

    #[Test]
    public function it_updates_a_specific_sites_tree()
    {
        $this->markTestIncomplete();
    }
}
