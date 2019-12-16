<?php

namespace Tests\Feature\Entries;

use Tests\TestCase;
use Tests\FakesRoles;
use Statamic\Facades\User;
use Statamic\Facades\Entry;
use Statamic\Facades\Collection;
use Tests\PreventSavingStacheItemsToDisk;
use Facades\Tests\Factories\EntryFactory;

class ReorderEntriesTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        $this->collection = Collection::make('test')
            ->sites(['en'])
            ->orderable(true)
            ->save();
    }

    /** @test */
    function it_denies_access_if_you_dont_have_permission()
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

    /** @test */
    function it_denies_access_if_the_collection_is_not_orderable()
    {
        $this->setTestRoles(['test' => ['access cp', 'reorder test entries']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $this->collection->orderable(false)->save();

        $this
            ->from('/original')
            ->actingAs($user)
            ->reorder([])
            ->assertRedirect('/original')
            ->assertSessionHas('error');
    }

    /** @test */
    function it_reorders_entries()
    {
        EntryFactory::id('1')->slug('one')->collection('test')->order(1)->create();
        EntryFactory::id('2')->slug('two')->collection('test')->order(2)->create();
        EntryFactory::id('3')->slug('three')->collection('test')->order(3)->create();

        $this->setTestRoles(['test' => ['access cp', 'reorder test entries']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $this
            ->actingAs($user)
            ->reorder(['ids' => [3, 1, 2]])
            ->assertOk();

        $this->assertEquals(2, Entry::find(1)->order());
        $this->assertEquals(3, Entry::find(2)->order());
        $this->assertEquals(1, Entry::find(3)->order());
    }

    /** @test */
    function it_reorders_paginated_entries()
    {
        EntryFactory::id('1')->slug('one')->collection('test')->order(1)->create();
        EntryFactory::id('2')->slug('two')->collection('test')->order(2)->create();
        // page starts here
        EntryFactory::id('3')->slug('three')->collection('test')->order(3)->create();
        EntryFactory::id('4')->slug('four')->collection('test')->order(4)->create();
        EntryFactory::id('5')->slug('five')->collection('test')->order(5)->create();
        // ends here
        EntryFactory::id('6')->slug('six')->collection('test')->order(6)->create();

        $this->setTestRoles(['test' => ['access cp', 'reorder test entries']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $this
            ->actingAs($user)
            ->reorder(['ids' => [5, 3, 4]])
            ->assertOk();

        $this->assertEquals(1, Entry::find(1)->order());
        $this->assertEquals(2, Entry::find(2)->order());
        $this->assertEquals(4, Entry::find(3)->order());
        $this->assertEquals(5, Entry::find(4)->order());
        $this->assertEquals(3, Entry::find(5)->order());
        $this->assertEquals(6, Entry::find(6)->order());
    }

    private function reorder($payload)
    {
        return $this->post(cp_route('collections.entries.reorder', 'test'), $payload);
    }
}
