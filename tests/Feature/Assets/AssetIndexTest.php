<?php

namespace Tests\Feature\Assets;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class AssetIndexTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_redirects_to_the_first_authorized_containers_browse_url()
    {
        $this->setTestRoles(['test' => ['access cp', 'view two assets']]);
        $user = User::make()->assignRole('test')->save();
        $containerOne = AssetContainer::make('one')->save();
        $containerTwo = AssetContainer::make('two')->save();

        $this
            ->actingAs($user)
            ->get(cp_route('assets.index'))
            ->assertRedirect($containerTwo->showUrl());
    }

    #[Test]
    public function it_shows_the_empty_state_if_there_are_no_containers_and_you_have_permission_to_create()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure asset containers']]);
        $user = User::make()->assignRole('test')->save();

        $this
            ->actingAs($user)
            ->get(cp_route('assets.index'))
            ->assertSuccessful()
            ->assertSee('Create Asset Container');
    }

    #[Test]
    public function it_denies_access_if_there_are_no_containers_and_you_dont_have_permission_to_create()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = User::make()->assignRole('test')->save();

        $this
            ->actingAs($user)
            ->from('/original')
            ->get(cp_route('assets.index'))
            ->assertRedirect('/original');
    }
}
