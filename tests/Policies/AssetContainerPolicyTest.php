<?php

namespace Tests\Policies;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Assets\AssetContainer as ContainerContract;
use Statamic\Facades\AssetContainer;

class AssetContainerPolicyTest extends PolicyTestCase
{
    #[Test]
    public function it_can_be_viewed()
    {
        $user = $this->userWithPermissions(['view alfa assets']);
        $containerA = tap(AssetContainer::make('alfa'))->save();
        $containerB = tap(AssetContainer::make('bravo'))->save();

        $this->assertTrue($user->can('view', $containerA));
        $this->assertFalse($user->can('view', $containerB));
    }

    #[Test]
    public function it_can_only_see_index_if_there_are_authorized_containers()
    {
        $userAlfa = $this->userWithPermissions(['view alfa assets']);
        $userBravo = $this->userWithPermissions(['view bravo assets']);
        $userAlfaBravo = $this->userWithPermissions(['view alfa assets', 'view bravo assets']);
        $userNone = $this->userWithPermissions([]);

        tap(AssetContainer::make('alfa'))->save();
        tap(AssetContainer::make('bravo'))->save();

        $this->assertTrue($userAlfa->can('index', [ContainerContract::class]));
        $this->assertTrue($userBravo->can('index', [ContainerContract::class]));
        $this->assertTrue($userAlfaBravo->can('index', [ContainerContract::class]));
        $this->assertFalse($userNone->can('index', [ContainerContract::class]));
    }

    #[Test]
    public function configure_permission_grants_access_to_everything_else()
    {
        $userWithPermission = $this->userWithPermissions(['configure asset containers']);
        $userWithoutConfigurePermission = $this->userWithPermissions(['view alfa assets']);
        $userWithoutAnyPermissions = $this->userWithPermissions([]);

        $container = tap(AssetContainer::make('alfa'))->save();

        $this->assertTrue($userWithPermission->can('view', $container));
        $this->assertTrue($userWithPermission->can('index', [ContainerContract::class]));
        $this->assertTrue($userWithPermission->can('create', [ContainerContract::class]));
        $this->assertTrue($userWithPermission->can('edit', $container));
        $this->assertTrue($userWithPermission->can('update', $container));
        $this->assertTrue($userWithPermission->can('delete', $container));

        $this->assertTrue($userWithoutConfigurePermission->can('view', $container));
        $this->assertTrue($userWithoutConfigurePermission->can('index', [ContainerContract::class]));
        $this->assertFalse($userWithoutConfigurePermission->can('create', [ContainerContract::class]));
        $this->assertFalse($userWithoutConfigurePermission->can('edit', $container));
        $this->assertFalse($userWithoutConfigurePermission->can('update', $container));
        $this->assertFalse($userWithoutConfigurePermission->can('delete', $container));

        $this->assertFalse($userWithoutAnyPermissions->can('view', $container));
        $this->assertFalse($userWithoutAnyPermissions->can('index', [ContainerContract::class]));
        $this->assertFalse($userWithoutAnyPermissions->can('create', [ContainerContract::class]));
        $this->assertFalse($userWithoutAnyPermissions->can('edit', $container));
        $this->assertFalse($userWithoutAnyPermissions->can('update', $container));
        $this->assertFalse($userWithoutAnyPermissions->can('delete', $container));
    }
}
