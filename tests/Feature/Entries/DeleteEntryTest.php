<?php

namespace Tests\Feature\Entries;

use Facades\Tests\Factories\EntryFactory;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class DeleteEntryTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_deletes_entries()
    {
        $user = tap(User::make('test')->makeSuper())->save();
        EntryFactory::id('1')->slug('one')->collection('test')->create();
        EntryFactory::id('2')->slug('two')->collection('test')->create();
        EntryFactory::id('3')->slug('three')->collection('test')->create();

        $this->assertCount(3, Entry::all());

        $this
            ->actingAs($user)
            ->deleteEntries(['1', '3'])
            ->assertOk();

        $this->assertCount(1, Entry::all());
        $this->assertEquals('two', Entry::all()->first()->slug());
    }

    #[Test]
    public function entries_get_removed_from_the_structure_tree_and_child_pages_are_moved_to_the_parent()
    {
        $this->withoutExceptionHandling();
        $user = tap(User::make('test')->makeSuper())->save();
        EntryFactory::id('1')->slug('one')->collection('test')->create();
        EntryFactory::id('2')->slug('two')->collection('test')->create();
        EntryFactory::id('3')->slug('three')->collection('test')->create();
        EntryFactory::id('4')->slug('four')->collection('test')->create();
        EntryFactory::id('5')->slug('five')->collection('test')->create();
        EntryFactory::id('6')->slug('six')->collection('test')->create();
        EntryFactory::id('7')->slug('six')->collection('test')->create();
        EntryFactory::id('8')->slug('six')->collection('test')->create();
        EntryFactory::id('9')->slug('six')->collection('test')->create();

        $collection = tap(Collection::findByHandle('test')->structureContents([
            'max_depth' => 10, // irrelevant to test. just cant pass in an empty array at the moment.
        ]))->save();

        $collection->structure()->in('en')->tree(
            [
                ['entry' => '1', 'children' => [
                    ['entry' => '4'],
                    ['entry' => '5', 'children' => [
                        ['entry' => '6'],
                    ]],
                ]],
                ['entry' => '2'],
                ['entry' => '3', 'children' => [
                    ['entry' => '7', 'children' => [
                        ['entry' => '8', 'children' => [
                            ['entry' => '9'],
                        ]],
                    ]],
                ]],
            ]
        )->save();
        $originalStructure = $collection->structure();
        $originalTree = $originalStructure->in('en');

        $this->assertCount(9, Entry::all());

        $this
            ->actingAs($user)
            ->deleteEntries(['1', '2', '7'])
            ->assertOk();

        $updatedCollection = Collection::findByHandle('test');
        $updatedStructure = $updatedCollection->structure();
        $updatedTree = $updatedStructure->in('en')->tree();

        // TODO: Ideally, the order would be maintained and the assertion in the following test could
        // go right here and pass.
        //
        // However, it's currently rearranging the tree items as the entries are deleted, in the order
        // they are submitted. These assertions are proving that the items are *structured* correctly
        // (ie. they'd result in the same urls, minus the parent slug) but not necessarily in the same order.
        $this->assertEquals(['entry' => '4'], collect($updatedTree)->first(function ($item) {
            return $item['entry'] == '4';
        }));

        $this->assertEquals([
            'entry' => '3', 'children' => [
                ['entry' => '8', 'children' => [
                    ['entry' => '9'],
                ]],
            ],
        ], collect($updatedTree)->first(function ($item) {
            return $item['entry'] == '3';
        }));

        $this->assertEquals([
            'entry' => '5', 'children' => [
                ['entry' => '6'],
            ],
        ], collect($updatedTree)->first(function ($item) {
            return $item['entry'] == '5';
        }));

        $this->assertNotSame($originalTree, $updatedTree);
    }

    #[Test]
    public function entries_get_removed_from_the_structure_and_child_pages_are_moved_to_the_parent_and_maintain_order()
    {
        // TODO: This is a reminder that the previous test needs to have the following assertion swapped in.
        $this->markTestIncomplete();

        // $this->assertEquals([
        //     ['entry' => '4'],
        //     ['entry' => '5', 'children' => [
        //         ['entry' => '6']
        //     ]],
        //     ['entry' => '3', 'children' => [
        //         ['entry' => '8', 'children' => [
        //             ['entry' => '9']
        //         ]],
        //     ]],
        // ], $updatedTree);
    }

    public function deleteEntries($ids)
    {
        return $this->postJson('/cp/collections/test/entries/actions', [
            'action' => 'delete',
            'context' => ['collection' => 'test'],
            'selections' => $ids,
            'values' => [],
        ]);
    }
}
