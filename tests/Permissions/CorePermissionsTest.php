<?php

namespace Tests\Permissions;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Permission;
use Tests\TestCase;

class CorePermissionsTest extends TestCase
{
    public static function supersededPermissionProvider()
    {
        return [
            'entries' => ['view {collection} entries', 'configure collections'],
            'navs' => ['view {nav} nav', 'configure navs'],
            'globals' => ['edit {global} globals', 'configure globals'],
            'terms' => ['view {taxonomy} terms', 'configure taxonomies'],
            'assets' => ['view {container} assets', 'configure asset containers'],
            'form submissions' => ['view {form} form submissions', 'configure forms'],
        ];
    }

    #[Test]
    #[DataProvider('supersededPermissionProvider')]
    public function configure_permissions_supersede_their_per_item_permissions($permission, $superseder)
    {
        $this->assertEquals($superseder, Permission::boot()->get($permission)->supersededBy());
    }

    #[Test]
    public function child_permissions_are_not_superseded_since_their_parents_are()
    {
        $this->assertNull(Permission::boot()->get('edit {collection} entries')->supersededBy());
        $this->assertNull(Permission::boot()->get('delete {container} assets')->supersededBy());
    }

    #[Test]
    public function site_access_is_not_superseded_by_configuring_sites()
    {
        $this->setSites([
            'en' => ['url' => '/', 'locale' => 'en_US', 'name' => 'English'],
            'fr' => ['url' => '/fr/', 'locale' => 'fr_FR', 'name' => 'French'],
        ]);

        $this->assertNull(Permission::boot()->get('access {site} site')->supersededBy());
    }
}
