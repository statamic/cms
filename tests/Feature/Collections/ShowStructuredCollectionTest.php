<?php

namespace Tests\Feature\Collections;

use Facades\Tests\Factories\EntryFactory;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;

class ShowStructuredCollectionTest extends ShowCollectionTestCase
{
    use PreventSavingStacheItemsToDisk;

    public function createCollection($handle)
    {
        $collection = tap(Collection::make('test')->structureContents(['max_depth' => 10]))->save();
        $collection->structure()->makeTree('en')->save();

        return $collection;
    }

    #[Test]
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
