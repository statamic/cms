<?php

namespace Tests\Feature\Navigation;

use Statamic\Facades;
use Statamic\Facades\Nav;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class EditNavigationTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;
    use MocksStructures;

    /** @test */
    public function it_shows_the_edit_form_if_user_has_edit_permission()
    {
        $nav = $this->createNav('foo');
        Nav::shouldReceive('all')->andReturn(collect([$nav]));
        Nav::shouldReceive('find')->andReturn($nav);

        $this->setTestRoles(['test' => ['access cp', 'edit foo nav']]);
        $user = Facades\User::make()->assignRole('test')->save();

        $response = $this
            ->actingAs($user)
            ->visitEditPage($nav)
            ->assertSuccessful()
            ->assertViewHas('nav', $nav);
    }

    /** @test */
    public function it_denies_access_if_user_doesnt_have_edit_permission()
    {
        $nav = $this->createNav('foo');
        Nav::shouldReceive('all')->andReturn(collect([$nav]));
        Nav::shouldReceive('find')->andReturn($nav);

        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(Facades\User::make()->assignRole('test'))->save();

        $response = $this
            ->from('/cp/original')
            ->actingAs($user)
            ->visitEditPage($nav)
            ->assertRedirect('/cp/original')
            ->assertSessionHas('error', 'You are not authorized to configure navs.');
    }

    public function visitEditPage($nav)
    {
        return $this->get(route('statamic.cp.navigation.edit', $nav->handle()));
    }
}
