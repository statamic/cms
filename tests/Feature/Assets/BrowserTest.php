<?php

namespace Tests\Feature\Assets;

use Statamic\Facades\AssetContainer;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class BrowserTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    /** @test */
    function it_redirects_to_the_first_container_from_the_index()
    {
        $this->setTestRoles(['test' => ['access cp', 'view one assets', 'view two assets']]);
        $user = User::make()->assignRole('test')->save();
        $containerOne = AssetContainer::make('one')->save();
        $containerTwo = AssetContainer::make('two')->save();

        $this
            ->actingAs($user)
            ->get(cp_route('assets.browse.index'))
            ->assertRedirect($containerOne->showUrl());
    }

    /** @test */
    function it_redirects_to_the_first_authorized_container_from_the_index()
    {
        $this->setTestRoles(['test' => ['access cp', 'view two assets']]);
        $user = User::make()->assignRole('test')->save();
        $containerOne = AssetContainer::make('one')->save();
        $containerTwo = AssetContainer::make('two')->save();

        $this
            ->actingAs($user)
            ->get(cp_route('assets.browse.index'))
            ->assertRedirect($containerTwo->showUrl());
    }

    /** @test */
    function no_authorized_containers_results_in_a_403_from_the_index()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = User::make()->assignRole('test')->save();
        $containerOne = AssetContainer::make('one')->save();
        $containerTwo = AssetContainer::make('two')->save();

        $this
            ->from('/original')
            ->actingAs($user)
            ->get(cp_route('assets.browse.index'))
            ->assertRedirect('/original');
    }

    /** @test */
    function no_containers_at_all_results_in_a_403_from_the_index()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = User::make()->assignRole('test')->save();

        $this
            ->from('/original')
            ->actingAs($user)
            ->get(cp_route('assets.browse.index'))
            ->assertRedirect('/original');
    }

    /** @test */
    function no_containers_but_permission_to_create_redirects_to_the_index()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure asset containers']]);
        $user = User::make()->assignRole('test')->save();

        $this
            ->actingAs($user)
            ->get(cp_route('assets.browse.index'))
            ->assertRedirect(cp_route('assets.index'));
    }

    /** @test */
    function it_denies_access()
    {
        $container = AssetContainer::make('test')->save();

        $this
            ->from('/original')
            ->actingAs($this->userWithoutPermission())
            ->get($container->showUrl())
            ->assertRedirect('/original');
    }

    /** @test */
    function it_shows_the_page()
    {
        $container = AssetContainer::make('test')->save();

        $this
            ->actingAs($this->userWithPermission())
            ->get($container->showUrl())
            ->assertSuccessful();
    }

    private function userWithPermission()
    {
        $this->setTestRoles(['test' => ['access cp', 'view test assets']]);

        return User::make()->assignRole('test')->save();
    }

    private function userWithoutPermission()
    {
        $this->setTestRoles(['test' => ['access cp']]);

        return User::make()->assignRole('test')->save();
    }
}