<?php

namespace Tests\Permissions;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Permission;
use Tests\TestCase;

class CorePermissionsTest extends TestCase
{
    public static function hiddenPermissionProvider()
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
    #[DataProvider('hiddenPermissionProvider')]
    public function configure_permissions_hide_their_per_item_permissions($permission, $hider)
    {
        $this->assertEquals([$hider], Permission::boot()->get($permission)->hiddenBy());
    }

    #[Test]
    public function child_permissions_are_not_hidden_since_their_parents_are()
    {
        $this->assertEquals([], Permission::boot()->get('edit {collection} entries')->hiddenBy());
        $this->assertEquals([], Permission::boot()->get('delete {container} assets')->hiddenBy());
    }

    #[Test]
    public function site_access_is_not_hidden_by_configuring_sites()
    {
        $this->setSites([
            'en' => ['url' => '/', 'locale' => 'en_US', 'name' => 'English'],
            'fr' => ['url' => '/fr/', 'locale' => 'fr_FR', 'name' => 'French'],
        ]);

        $this->assertEquals([], Permission::boot()->get('access {site} site')->hiddenBy());
    }
}
