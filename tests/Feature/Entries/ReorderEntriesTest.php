<?php

namespace Tests\Feature\Entries;

use Facades\Tests\Factories\EntryFactory;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Stache;
use Statamic\Facades\User;
use Statamic\Structures\CollectionStructure;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ReorderEntriesTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    private $structure;
    private $collection;

    public function setUp(): void
    {
        parent::setUp();

        $this->structure = (new CollectionStructure)->handle('test')->maxDepth(1);

        $this->collection = Collection::make('test')
            ->sites(['en'])
            ->structure($this->structure)
            ->save();

        $this->structure->makeTree('en')->save();
    }

    #[Test]
    public function it_denies_access_if_you_dont_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $this
            ->from('/original')
            ->actingAs($user)
            ->reorder([])
            ->assertRedirect('/original')
            ->assertSessionHas('error');
    }

    #[Test]
    public function it_denies_access_if_the_collection_is_not_orderable()
    {
        $this->setTestRoles(['test' => ['access cp', 'reorder test entries']]);
        $user = tap(User::make()->assignRole('test'))->save();

        Collection::make('test')->sites(['en'])->save();

        $this
            ->from('/original')
            ->actingAs($user)
            ->reorder([])
            ->assertRedirect('/original')
            ->assertSessionHas('error');
    }

    #[Test]
    public function it_reorders_entries()
    {
        EntryFactory::id('1')->slug('one')->collection('test')->create();
        EntryFactory::id('2')->slug('two')->collection('test')->create();
        EntryFactory::id('3')->slug('three')->collection('test')->create();

        $this->structure->in('en')->tree([
            ['entry' => '1'],
            ['entry' => '2'],
            ['entry' => '3'],
        ])->save();

        $this->setTestRoles(['test' => ['access cp', 'reorder test entries']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $this
            ->actingAs($user)
            ->reorder(['page' => 1, 'perPage' => 3, 'ids' => [3, 1, 2]])
            ->assertOk();

        $this->assertEquals([
            ['entry' => '3'],
            ['entry' => '1'],
            ['entry' => '2'],
        ], $this->structure->in('en')->tree());
        $this->assertEquals(2, Entry::find(1)->order());
        $this->assertEquals(3, Entry::find(2)->order());
        $this->assertEquals(1, Entry::find(3)->order());
    }

    #[Test]
    public function it_reorders_paginated_entries()
    {
        EntryFactory::id('1')->slug('one')->collection('test')->create();
        EntryFactory::id('2')->slug('two')->collection('test')->create();
        EntryFactory::id('3')->slug('three')->collection('test')->create();
        // page starts here
        EntryFactory::id('4')->slug('four')->collection('test')->create();
        EntryFactory::id('5')->slug('five')->collection('test')->create();
        EntryFactory::id('6')->slug('six')->collection('test')->create();
        // ends here
        EntryFactory::id('7')->slug('seven')->collection('test')->create();

        $this->structure->in('en')->tree([
            ['entry' => '1'],
            ['entry' => '2'],
            ['entry' => '3'],
            ['entry' => '4'],
            ['entry' => '5'],
            ['entry' => '6'],
            ['entry' => '7'],
        ])->save();

        $this->setTestRoles(['test' => ['access cp', 'reorder test entries']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $this
            ->actingAs($user)
            ->reorder(['page' => 2, 'perPage' => 3, 'ids' => [6, 4, 5]])
            ->assertOk();

        $this->assertEquals([
            ['entry' => '1'],
            ['entry' => '2'],
            ['entry' => '3'],
            ['entry' => '6'],
            ['entry' => '4'],
            ['entry' => '5'],
            ['entry' => '7'],
        ], $this->structure->in('en')->tree());
        $this->assertEquals(1, Entry::find(1)->order());
        $this->assertEquals(2, Entry::find(2)->order());
        $this->assertEquals(3, Entry::find(3)->order());
        $this->assertEquals(4, Entry::find(6)->order());
        $this->assertEquals(5, Entry::find(4)->order());
        $this->assertEquals(6, Entry::find(5)->order());
        $this->assertEquals(7, Entry::find(7)->order());
    }

    #[Test]
    public function it_reorders_paginated_entries_in_a_descending_collection()
    {
        $this->collection->sortDirection('desc')->save();

        EntryFactory::id('1')->slug('one')->collection('test')->create();
        EntryFactory::id('2')->slug('two')->collection('test')->create();
        EntryFactory::id('3')->slug('three')->collection('test')->create();
        EntryFactory::id('4')->slug('four')->collection('test')->create();
        EntryFactory::id('5')->slug('five')->collection('test')->create();

        $this->structure->in('en')->tree([
            ['entry' => '1'],
            ['entry' => '2'],
            ['entry' => '3'],
            ['entry' => '4'],
            ['entry' => '5'],
        ])->save();

        $this->setTestRoles(['test' => ['access cp', 'reorder test entries']]);
        $user = tap(User::make()->assignRole('test'))->save();

        // The listing shows 5, 4, 3, 2, 1. The first page contains 5, 4, 3.
        $this
            ->actingAs($user)
            ->reorder(['page' => 1, 'perPage' => 3, 'ids' => [3, 5, 4]])
            ->assertOk();

        $this->assertEquals([
            ['entry' => '1'],
            ['entry' => '2'],
            ['entry' => '4'],
            ['entry' => '5'],
            ['entry' => '3'],
        ], $this->structure->in('en')->tree());
    }

    #[Test]
    public function it_doesnt_reorder_when_the_submitted_entries_arent_on_the_page_being_reordered()
    {
        EntryFactory::id('1')->slug('one')->collection('test')->create();
        EntryFactory::id('2')->slug('two')->collection('test')->create();
        EntryFactory::id('3')->slug('three')->collection('test')->create();
        EntryFactory::id('4')->slug('four')->collection('test')->create();

        $tree = [
            ['entry' => '1'],
            ['entry' => '2'],
            ['entry' => '3'],
            ['entry' => '4'],
        ];

        $this->structure->in('en')->tree($tree)->save();

        $this->setTestRoles(['test' => ['access cp', 'reorder test entries']]);
        $user = tap(User::make()->assignRole('test'))->save();

        // The first page of the tree is 1, 2, 3, but the listing was showing 4 in there.
        $this
            ->actingAs($user)
            ->reorder(['page' => 1, 'perPage' => 3, 'ids' => [1, 4, 2]])
            ->assertStatus(409);

        $this->assertEquals($tree, $this->structure->in('en')->tree());
    }

    #[Test]
    public function creating_an_entry_gives_it_the_correct_order_when_the_tree_has_already_been_read()
    {
        EntryFactory::id('1')->slug('one')->collection('test')->create();
        EntryFactory::id('2')->slug('two')->collection('test')->create();
        EntryFactory::id('3')->slug('three')->collection('test')->create();

        $this->structure->in('en')->tree([
            ['entry' => '1'],
            ['entry' => '2'],
            ['entry' => '3'],
        ])->save();

        // Read the tree before the entry is created, so it gets cached without it.
        $this->structure->in('en')->tree();

        EntryFactory::id('4')->slug('four')->collection('test')->create();

        $this->assertEquals([
            ['entry' => '1'],
            ['entry' => '2'],
            ['entry' => '3'],
            ['entry' => '4'],
        ], $this->structure->in('en')->tree());

        $this->assertEquals(4, Entry::find('4')->order());

        $this->assertEquals(
            ['1' => 1, '2' => 2, '3' => 3, '4' => 4],
            Stache::store('entries::test')->index('order')->items()->all()
        );
    }

    private function reorder($payload)
    {
        return $this->post(cp_route('collections.entries.reorder', 'test'), array_merge(['site' => 'en'], $payload));
    }
}
