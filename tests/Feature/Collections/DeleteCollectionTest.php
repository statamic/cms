<?php

namespace Tests\Feature\Collections;

use Tests\TestCase;
use Tests\FakesRoles;
use Statamic\API\User;
use Statamic\API\Collection;
use Tests\PreventSavingStacheItemsToDisk;

class DeleteCollectionTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    /** @test */
    function it_denies_access_if_you_dont_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = User::create('test')->get()->assignRole('test');

        $collection = Collection::create('test')->save();
        $this->assertCount(1, Collection::all());

        $this
            ->from('/original')
            ->actingAs($user)
            ->delete(cp_route('collections.destroy', $collection->path()))
            ->assertRedirect('/original')
            ->assertSessionHasErrors();

        $this->assertCount(1, Collection::all());
    }

    /** @test */
    function it_deletes_the_collection()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure collections']]);
        $user = User::create('test')->get()->assignRole('test');

        $collection = Collection::create('test')->save();
        $this->assertCount(1, Collection::all());

        $this
            ->actingAs($user)
            ->delete(cp_route('collections.destroy', $collection->path()))
            ->assertRedirect(cp_route('collections.index'))
            ->assertSessionHas('success');

        $this->assertCount(0, Collection::all());
    }
}
