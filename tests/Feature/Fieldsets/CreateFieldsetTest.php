<?php

namespace Tests\Feature\Fieldsets;

use Statamic\Facades;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class CreateFieldsetTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function it_denies_access_if_you_dont_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(Facades\User::make()->assignRole('test'))->save();

        $this
            ->from('/original')
            ->actingAs($user)
            ->get(cp_route('fieldsets.create'))
            ->assertRedirect('/original')
            ->assertSessionHas('error');
    }
}
