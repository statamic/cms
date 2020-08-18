<?php

namespace Tests\Feature\Collections;

use Facades\Tests\Factories\EntryFactory;
use Statamic\Facades\Collection;
use Statamic\Facades\User;
use Statamic\Structures\CollectionStructure;
use Tests\PreventSavingStacheItemsToDisk;

class ShowStructuredCollectionTest extends ShowCollectionTest
{
    use PreventSavingStacheItemsToDisk;

    public function createCollection($handle)
    {
        $structure = (new CollectionStructure)->tap(function ($s) {
            $s->addTree($s->makeTree('en'));
        });

        return tap(Collection::make('test')->structure($structure))->save();
    }

    /** @test */
    public function it_shows_the_structure_tree_if_you_have_permission()
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
