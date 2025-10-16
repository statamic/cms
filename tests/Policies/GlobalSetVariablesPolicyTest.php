<?php

namespace Tests\Policies;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Globals\Variables;
use Statamic\Facades\GlobalSet;

class GlobalSetVariablesPolicyTest extends PolicyTestCase
{
    #[Test]
    public function variables_are_editable_with_edit_permissions()
    {
        $user = $this->userWithPermissions(['edit test globals']);

        $global = tap(GlobalSet::make('test'))->save();

        $this->assertTrue($user->can('edit', $global->inDefaultSite()));
        $this->assertTrue($user->can('view', $global->inDefaultSite()));
    }

    #[Test]
    public function variables_are_editable_with_edit_and_site_permissions()
    {
        $this->withSites(['en', 'fr', 'de']);

        $user = $this->userWithPermissions([
            'edit test globals',
            'access en site',
            // 'access fr site', // Intentionally missing.
            'access de site',
        ]);

        $global = tap(GlobalSet::make('test')->sites(['en', 'fr', 'de']))->save();

        $this->assertTrue($user->can('edit', $global->in('en')));
        $this->assertTrue($user->can('view', $global->in('en')));
        $this->assertFalse($user->can('edit', $global->in('fr')));
        $this->assertFalse($user->can('view', $global->in('fr')));
        $this->assertTrue($user->can('edit', $global->in('de')));
        $this->assertTrue($user->can('view', $global->in('de')));
    }

    #[Test]
    public function variables_can_be_created_with_configure_permission()
    {
        $forbiddenUser = $this->userWithPermissions([]);
        $authorizedUser = $this->userWithPermissions(['configure globals']);

        $this->assertTrue($authorizedUser->can('create', Variables::class));
        $this->assertFalse($forbiddenUser->can('create', Variables::class));
    }

    #[Test]
    public function variables_can_be_deleted_with_configure_permission()
    {
        $forbiddenUser = $this->userWithPermissions([]);
        $authorizedUser = $this->userWithPermissions(['configure globals']);
        $global = tap(GlobalSet::make('test'))->save();
        $variables = $global->inDefaultSite();

        $this->assertTrue($authorizedUser->can('delete', $variables));
        $this->assertFalse($forbiddenUser->can('delete', $variables));
    }
}
