<?php

namespace Tests\Feature\Collections;

use Tests\TestCase;
use Tests\FakesRoles;
use Statamic\API\User;
use Statamic\API\Collection;
use Tests\PreventSavingStacheItemsToDisk;

class CreateCollectionTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    /** @test */
    function it_shows_the_create_page_if_you_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure collections']]);
        $user = User::make()->assignRole('test');

        $this
            ->actingAs($user)
            ->get(cp_route('collections.create'))
            ->assertSuccessful();
    }

    /** @test */
    function it_denies_access_if_you_dont_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = User::make()->assignRole('test');

        $this
            ->from('/original')
            ->actingAs($user)
            ->get(cp_route('collections.create'))
            ->assertRedirect('/original')
            ->assertSessionHasErrors();
    }
}
