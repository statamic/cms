<?php

namespace Tests\Feature\Collections;

use Facades\Tests\Factories\EntryFactory;
use Statamic\Facades\User;
use Statamic\Facades\Collection;
use Tests\PreventSavingStacheItemsToDisk;

class ShowRegularCollectionTest extends ShowCollectionTest
{
    use PreventSavingStacheItemsToDisk;

    function createCollection($handle)
    {
        return tap(Collection::make('test'))->save();
    }

    /** @test */
    function it_shows_the_entry_listing_page_if_you_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp', 'view test entries']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $collection = $this->createCollection('test');
        EntryFactory::id('1')->collection($collection)->create();

        $this
            ->actingAs($user)
            ->get($collection->showUrl())
            ->assertOk()
            ->assertViewIs('statamic::collections.show')
            ->assertViewHas('collection', $collection);
    }
}
