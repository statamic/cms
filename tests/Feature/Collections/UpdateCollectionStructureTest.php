<?php

namespace Tests\Feature\Collections;

use Facades\Tests\Factories\EntryFactory;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class UpdateCollectionStructureTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;
    use FakesRoles;

    /** @test */
    function it_updates_the_tree()
    {
        $user = tap(User::make()->makeSuper())->save();
        $collection = tap(Collection::make('test'))->save();
        EntryFactory::id('1')->collection($collection)->create();
        EntryFactory::id('2')->collection($collection)->create();
        EntryFactory::id('3')->collection($collection)->create();
        EntryFactory::id('4')->collection($collection)->create();
        EntryFactory::id('5')->collection($collection)->create();
        $collection->structureContents(['tree' => [
            ['entry' => '1'],
            ['entry' => '2'],
            ['entry' => '3'],
            ['entry' => '4'],
            ['entry' => '5'],
        ]])->save();

        $this
            ->actingAs($user)
            ->update($collection, ['pages' => [
                ['id' => '3', 'children' => [
                    ['id' => '5', 'children' => [
                        ['id' => '4', 'children' => []]
                    ]]
                ]],
                ['id' => '1', 'children' => []],
                ['id' => '2', 'children' => []],
            ]])
            ->assertOk();

        $this->assertEquals([
            ['entry' => '3', 'children' => [
                ['entry' => '5', 'children' => [
                    ['entry' => '4']
                ]]
            ]],
            ['entry' => '1'],
            ['entry' => '2'],
        ], $collection->structure()->in('en')->tree());
    }

    /** @test */
    function it_deletes_entries_scheduled_for_deletion()
    {
        $user = tap(User::make()->makeSuper())->save();
        $collection = tap(Collection::make('test'))->save();
        EntryFactory::id('1')->collection($collection)->create();
        EntryFactory::id('2')->collection($collection)->create();
        EntryFactory::id('3')->collection($collection)->create();
        $collection->structureContents(['tree' => []])->save();
        $this->assertCount(3, Entry::all());

        $this
            ->actingAs($user)
            ->update($collection, ['deletedEntries' => ['1', '3']])
            ->assertOk();

        $this->assertCount(1, Entry::all());
    }

    /** @test */
    function it_denies_access_if_you_dont_have_permission_to_reorder()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $collection = tap(Collection::make('test')->structureContents(['tree' => []]))->save();

        $this
            ->actingAs($user)
            ->update($collection, ['site' => 'en', 'pages' => []])
            ->assertForbidden();
    }

    function update($collection, $payload = [])
    {
        $validParams = [
            'site' => 'en',
            'pages' => [],
            'deletedEntries' => []
        ];

        return $this->postJson(
            cp_route('collections.structure.update', $collection->handle()),
            array_merge($validParams, $payload)
        );
    }

    /** @test */
    function it_updates_a_specific_sites_tree()
    {
        $this->markTestIncomplete();
    }
}
