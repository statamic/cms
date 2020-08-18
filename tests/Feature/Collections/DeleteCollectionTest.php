<?php

namespace Tests\Feature\Collections;

use Statamic\Facades\Collection;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class DeleteCollectionTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function it_denies_access_if_you_dont_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $collection = Collection::make('test')->save();
        $this->assertCount(1, Collection::all());

        $this
            ->from('/original')
            ->actingAs($user)
            ->delete(cp_route('collections.destroy', $collection->handle()))
            ->assertRedirect('/original')
            ->assertSessionHas('error');

        $this->assertCount(1, Collection::all());
    }

    /** @test */
    public function it_deletes_the_collection()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure collections']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $collection = Collection::make('test')->save();
        $this->assertCount(1, Collection::all());

        $this
            ->actingAs($user)
            ->delete(cp_route('collections.destroy', $collection->handle()))
            ->assertOk();

        $this->assertCount(0, Collection::all());
    }
}
