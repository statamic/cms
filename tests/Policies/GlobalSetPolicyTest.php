<?php

namespace Tests\Policies;

use Facades\Tests\Factories\GlobalFactory;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Globals\GlobalSet;

class GlobalSetPolicyTest extends PolicyTestCase
{
    #[Test]
    public function index_is_allowed_if_any_set_is_viewable()
    {
        $userWithAlfaPermission = $this->userWithPermissions(['edit alfa globals']);
        $userWithBravoPermission = $this->userWithPermissions(['edit bravo globals']);
        $userWithConfigurePermission = $this->userWithPermissions(['configure globals']);
        $userWithoutPermission = $this->userWithPermissions([]);

        GlobalFactory::handle('alfa')->data(['foo' => 'bar'])->create();
        GlobalFactory::handle('bravo')->data(['foo' => 'bar'])->create();

        $this->assertTrue($userWithAlfaPermission->can('index', GlobalSet::class));
        $this->assertTrue($userWithBravoPermission->can('index', GlobalSet::class));
        $this->assertTrue($userWithConfigurePermission->can('index', GlobalSet::class));
        $this->assertFalse($userWithoutPermission->can('index', GlobalSet::class));
    }

    #[Test]
    public function index_is_allowed_if_any_set_is_viewable_with_site_permissions()
    {
        $this->withSites(['en', 'fr', 'de']);

        $userWithEnPermission = $this->userWithPermissions([
            'edit test globals',
            'access en site',
        ]);
        $userWithDePermission = $this->userWithPermissions([
            'edit test globals',
            'access de site',
        ]);

        $global = GlobalFactory::handle('test')->data(['foo' => 'bar'])->create();
        $global->addLocalization($global->makeLocalization('en'))->save();
        $global->addLocalization($global->makeLocalization('fr'))->save();

        $this->assertTrue($userWithEnPermission->can('index', GlobalSet::class));
        $this->assertFalse($userWithDePermission->can('index', GlobalSet::class));
    }

    #[Test]
    public function globals_are_viewable_with_edit_permissions()
    {
        $user = $this->userWithPermissions(['edit test globals']);

        $global = GlobalFactory::handle('test')->data(['foo' => 'bar'])->create();

        $this->assertTrue($user->can('view', $global));
        $this->assertFalse($user->can('edit', $global));
    }

    #[Test]
    public function globals_are_editable_with_configure_permissions()
    {
        $authorizedUser = $this->userWithPermissions(['configure globals']);
        $forbiddenUser = $this->userWithPermissions(['edit test globals']);

        $global = GlobalFactory::handle('test')->data(['foo' => 'bar'])->create();

        $this->assertTrue($authorizedUser->can('edit', $global));
        $this->assertFalse($forbiddenUser->can('edit', $global));
    }

    #[Test]
    public function globals_can_be_created_with_configure_permission()
    {
        $forbiddenUser = $this->userWithPermissions([]);
        $authorizedUser = $this->userWithPermissions(['configure globals']);

        $this->assertTrue($authorizedUser->can('create', GlobalSet::class));
        $this->assertFalse($forbiddenUser->can('create', GlobalSet::class));
    }

    #[Test]
    public function globals_can_be_deleted_with_configure_permission()
    {
        $forbiddenUser = $this->userWithPermissions([]);
        $authorizedUser = $this->userWithPermissions(['configure globals']);
        $global = GlobalFactory::handle('test')->data(['foo' => 'bar'])->create();

        $this->assertTrue($authorizedUser->can('delete', $global));
        $this->assertFalse($forbiddenUser->can('delete', $global));
    }
}
