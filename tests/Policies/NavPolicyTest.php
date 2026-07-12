<?php

namespace Tests\Policies;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Structures\Nav as NavContract;
use Statamic\Facades\Nav;

class NavPolicyTest extends PolicyTestCase
{
    #[Test]
    public function index_is_allowed_if_any_nav_is_viewable()
    {
        $userWithAlfaPermission = $this->userWithPermissions(['view alfa nav']);
        $userWithBravoPermission = $this->userWithPermissions(['view bravo nav']);
        $userWithConfigurePermission = $this->userWithPermissions(['configure navs']);
        $userWithoutPermission = $this->userWithPermissions([]);

        Nav::make('alfa')->save();
        Nav::make('bravo')->save();

        $this->assertTrue($userWithAlfaPermission->can('index', NavContract::class));
        $this->assertTrue($userWithBravoPermission->can('index', NavContract::class));
        $this->assertTrue($userWithConfigurePermission->can('index', NavContract::class));
        $this->assertFalse($userWithoutPermission->can('index', NavContract::class));
    }

    #[Test]
    public function index_is_allowed_if_any_nav_is_viewable_with_site_permissions()
    {
        $this->withSites(['en', 'fr', 'de']);

        $userWithEnPermission = $this->userWithPermissions([
            'view test nav',
            'access en site',
        ]);
        $userWithDePermission = $this->userWithPermissions([
            'view test nav',
            'access de site',
        ]);

        $nav = tap(Nav::make('test'))->save();
        $nav->makeTree('en')->save();
        $nav->makeTree('fr')->save();

        $this->assertTrue($userWithEnPermission->can('index', NavContract::class));
        $this->assertFalse($userWithDePermission->can('index', NavContract::class));
    }

    #[Test]
    public function navs_are_viewable_with_view_permissions()
    {
        $user = $this->userWithPermissions(['view alfa nav']);

        $navA = Nav::make('alfa');
        $navB = Nav::make('bravo');

        $this->assertTrue($user->can('view', $navA));
        $this->assertFalse($user->can('edit', $navA));
        $this->assertFalse($user->can('view', $navB));
        $this->assertFalse($user->can('edit', $navB));
    }

    #[Test]
    public function navs_are_viewable_with_view_and_site_permissions()
    {
        $this->withSites(['en', 'fr', 'de']);

        $userWithEnPermission = $this->userWithPermissions([
            'view test nav',
            'access en site',
        ]);
        $userWithDePermission = $this->userWithPermissions([
            'view test nav',
            'access de site',
        ]);

        $nav = tap(Nav::make('test'))->save();
        $nav->makeTree('en')->save();
        $nav->makeTree('fr')->save();

        $this->assertTrue($userWithEnPermission->can('view', $nav));
        $this->assertFalse($userWithDePermission->can('view', $nav));
    }

    #[Test]
    public function navs_are_editable_with_edit_permissions()
    {
        $user = $this->userWithPermissions(['edit alfa nav']);

        $navA = Nav::make('alfa');
        $navB = Nav::make('bravo');

        $this->assertTrue($user->can('view', $navA));
        $this->assertTrue($user->can('edit', $navA));
        $this->assertFalse($user->can('view', $navB));
        $this->assertFalse($user->can('edit', $navB));
    }

    #[Test]
    public function navs_are_editable_with_edit_and_site_permissions()
    {
        $this->withSites(['en', 'fr', 'de']);

        $userWithEnPermission = $this->userWithPermissions([
            'edit test nav',
            'access en site',
        ]);
        $userWithDePermission = $this->userWithPermissions([
            'edit test nav',
            'access de site',
        ]);

        $nav = tap(Nav::make('test'))->save();
        $nav->makeTree('en')->save();
        $nav->makeTree('fr')->save();

        $this->assertTrue($userWithEnPermission->can('view', $nav));
        $this->assertTrue($userWithEnPermission->can('edit', $nav));
        $this->assertFalse($userWithDePermission->can('view', $nav));
        $this->assertFalse($userWithDePermission->can('edit', $nav));

    }

    #[Test]
    public function navs_can_be_created_with_configure_permission()
    {
        $forbiddenUser = $this->userWithPermissions([]);
        $authorizedUser = $this->userWithPermissions(['configure navs']);

        $this->assertTrue($authorizedUser->can('create', NavContract::class));
        $this->assertFalse($forbiddenUser->can('create', NavContract::class));
    }

    #[Test]
    public function navs_can_be_deleted_with_configure_permission()
    {
        $forbiddenUser = $this->userWithPermissions([]);
        $authorizedUser = $this->userWithPermissions(['configure navs']);
        $nav = Nav::make('test');

        $this->assertTrue($authorizedUser->can('delete', $nav));
        $this->assertFalse($forbiddenUser->can('delete', $nav));
    }
}
