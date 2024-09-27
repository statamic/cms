<?php

namespace Tests\Feature\Collections;

use Facades\Tests\Factories\EntryFactory;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;

class ShowRegularCollectionTest extends ShowCollectionTestCase
{
    use PreventSavingStacheItemsToDisk;

    public function createCollection($handle)
    {
        return tap(Collection::make('test'))->save();
    }

    #[Test]
    public function it_shows_the_entry_listing_page_if_you_have_permission()
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
