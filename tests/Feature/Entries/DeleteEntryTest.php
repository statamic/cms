<?php

namespace Tests\Feature\Entries;

use Facades\Tests\Factories\EntryFactory;
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

    /** @test */
    function it_deletes_entries()
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

    /** @test */
    function entries_get_removed_from_the_structure()
    {
        // We need to confirm that the structure actually gets saved, which would mean it becomes a new object.
        // When using the array cache driver, the same instance would always be returned.
        config(['cache.default' => 'file']);
        \Illuminate\Support\Facades\Cache::clear();

        $this->withoutExceptionHandling();
        $user = tap(User::make('test')->makeSuper())->save();
        EntryFactory::id('1')->slug('one')->collection('test')->create();
        EntryFactory::id('2')->slug('two')->collection('test')->create();
        EntryFactory::id('3')->slug('three')->collection('test')->create();

        $collection = tap(Collection::findByHandle('test')->structureContents([
            'tree' => [
                ['entry' => '1'],
                ['entry' => '2'],
                ['entry' => '3'],
            ]
        ]))->save();
        $originalStructure = $collection->structure();

        $this->assertCount(3, Entry::all());

        $this
            ->actingAs($user)
            ->deleteEntries(['1', '3'])
            ->assertOk();

        $updatedCollection = Collection::findByHandle('test');
        $updatedStructure = $updatedCollection->structure();
        $this->assertEquals([
            ['entry' => '2']
        ], $updatedStructure->in('en')->tree());
        $this->assertNotSame($originalStructure, $updatedStructure);
    }

    function deleteEntries($ids)
    {
        return $this->postJson('/cp/collections/test/entries/actions', [
            'action' => 'delete',
            'context' => ['collection' => 'test'],
            'selections' => $ids,
            'values' => [],
        ]);
    }
}
