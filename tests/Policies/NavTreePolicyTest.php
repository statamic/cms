<?php

namespace Tests\Policies;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Nav;

class NavTreePolicyTest extends PolicyTestCase
{
    #[Test]
    public function trees_are_viewable_with_view_permissions()
    {
        $user = $this->userWithPermissions(['view test nav']);

        $nav = Nav::make('test');
        $tree = $nav->makeTree('en');

        $this->assertTrue($user->can('view', $tree));
        $this->assertFalse($user->can('edit', $tree));
    }

    #[Test]
    public function trees_are_viewable_with_view_and_site_permissions()
    {
        $this->withSites(['en', 'fr', 'de']);

        $user = $this->userWithPermissions([
            'view test nav',
            'access en site',
            'access de site',
        ]);

        $nav = Nav::make('test');

        $this->assertTrue($user->can('view', $nav->makeTree('en')));
        $this->assertFalse($user->can('view', $nav->makeTree('fr')));
        $this->assertTrue($user->can('view', $nav->makeTree('de')));
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

        $user = $this->userWithPermissions([
            'edit test nav',
            'access en site',
            'access de site',
        ]);

        $nav = Nav::make('test');

        $this->assertTrue($user->can('edit', $nav->makeTree('en')));
        $this->assertFalse($user->can('edit', $nav->makeTree('fr')));
        $this->assertTrue($user->can('edit', $nav->makeTree('de')));
    }
}
