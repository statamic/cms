<?php

namespace Tests\Feature\Collections;

use Facades\Tests\Factories\EntryFactory;
use Statamic\Facades\User;
use Statamic\Facades\Collection;
use Statamic\Facades\Structure;
use Tests\PreventSavingStacheItemsToDisk;

class ShowStructuredCollectionTest extends ShowCollectionTest
{
    use PreventSavingStacheItemsToDisk;

    function createCollection($handle)
    {
        tap(Structure::make('test'), function ($s) {
            $s->addTree($s->makeTree('en'));
        })->save();

        return tap(Collection::make('test')->structure('test'))->save();
    }

    /** @test */
    function it_shows_the_structure_tree_if_you_have_permission()
    {
        $this->withoutExceptionHandling();
        $this->setTestRoles(['test' => ['access cp', 'view test entries']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $collection = $this->createCollection('test');
        EntryFactory::id('1')->collection($collection)->create();

        $this
            ->actingAs($user)
            ->get($collection->showUrl())
            ->assertOk()
            ->assertViewIs('statamic::collections.show')
            ->assertViewHas('collection', $collection)
            ->assertViewHas('structure', $collection->structure());
    }
}
