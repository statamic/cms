<?php

namespace Tests\Feature\Collections;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

abstract class ShowCollectionTestCase extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_shows_the_empty_entry_listing_page_if_you_have_permission_and_there_are_no_entries()
    {
        $this->setTestRoles(['test' => ['access cp', 'view test entries']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $collection = $this->createCollection('test');

        $this
            ->actingAs($user)
            ->get($collection->showUrl())
            ->assertOk()
            ->assertViewIs('statamic::collections.empty');
    }

    #[Test]
    public function it_denies_access_if_you_dont_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $collection = $this->createCollection('test');

        $this
            ->from('/original')
            ->actingAs($user)
            ->get($collection->showUrl())
            ->assertRedirect('/original')
            ->assertSessionHas('error');
    }

    abstract public function createCollection($handle);
}
