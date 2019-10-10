<?php

namespace Tests\Feature\Collections;

use Tests\TestCase;
use Tests\FakesRoles;
use Statamic\Facades\User;
use Statamic\Facades\Collection;
use Tests\PreventSavingStacheItemsToDisk;

class DeleteCollectionTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    /** @test */
    function it_denies_access_if_you_dont_have_permission()
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
    function it_deletes_the_collection()
    {
        $this->markTestIncomplete(); // TODO: Skipped until ->delete() is reimplemented

        $this->setTestRoles(['test' => ['access cp', 'configure collections']]);
        $user = User::make()->assignRole('test');

        $collection = Collection::make('test')->save();
        $this->assertCount(1, Collection::all());

        $this
            ->actingAs($user)
            ->delete(cp_route('collections.destroy', $collection->handle()))
            ->assertRedirect(cp_route('collections.index'))
            ->assertSessionHas('success');

        $this->assertCount(0, Collection::all());
    }
}
