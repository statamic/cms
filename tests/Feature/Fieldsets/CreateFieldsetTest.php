<?php

namespace Tests\Feature\Fieldsets;

use Statamic\API;
use Tests\TestCase;
use Tests\FakesRoles;

class CreateFieldsetTest extends TestCase
{
    use FakesRoles;

    /** @test */
    function it_denies_access_if_you_dont_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = API\User::create('test')->get()->assignRole('test');

        $this
            ->from('/original')
            ->actingAs($user)
            ->get(cp_route('fieldsets.create'))
            ->assertRedirect('/original')
            ->assertSessionHasErrors();
    }
}
