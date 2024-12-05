<?php

namespace Tests\Feature\AssetContainers;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class EditAssetContainerTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_shows_the_form()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure asset containers']]);
        $user = User::make()->assignRole('test')->save();
        $container = AssetContainer::make('test')->save();

        $this
            ->actingAs($user)
            ->get($container->editUrl())
            ->assertSuccessful();
    }

    #[Test]
    public function it_denies_access_if_you_dont_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = User::make()->assignRole('test')->save();
        $container = AssetContainer::make('test')->save();

        $this
            ->actingAs($user)
            ->from('/original')
            ->get($container->editUrl())
            ->assertRedirect('/original');
    }
}
