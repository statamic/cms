<?php

namespace Tests\Feature\Entries;

use Facades\Tests\Factories\EntryFactory;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
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

    private function reorder($payload)
    {
        return $this->post(cp_route('collections.entries.reorder', 'test'), array_merge(['site' => 'en'], $payload));
    }
}
