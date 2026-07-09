<?php

namespace Tests\Permissions;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Permission;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class CorePermissionsTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public static function hiddenPermissionProvider()
    {
        return [
            'entries' => ['view {collection} entries', 'configure collections'],
            'navs' => ['view {nav} nav', 'configure navs'],
            'globals' => ['edit {global} globals', 'configure globals'],
            'terms' => ['view {taxonomy} terms', 'configure taxonomies'],
            'assets' => ['view {container} assets', 'configure asset containers'],
            'form submissions' => ['view {form} form submissions', ['configure forms', 'view form submissions']],
        ];
    }

    #[Test]
    #[DataProvider('hiddenPermissionProvider')]
    public function configure_permissions_hide_their_per_item_permissions($permission, $hider)
    {
        $this->assertEquals((array) $hider, Permission::boot()->get($permission)->hiddenBy());
    }

    #[Test]
    public function child_permissions_are_not_hidden_since_their_parents_are()
    {
        $this->assertEquals([], Permission::boot()->get('edit {collection} entries')->hiddenBy());
        $this->assertEquals([], Permission::boot()->get('delete {container} assets')->hiddenBy());
    }

    #[Test]
    public function replaced_child_permissions_keep_their_descriptions()
    {
        Collection::make('blog')->save();

        $group = Permission::boot()->tree()->firstWhere('handle', 'collections');
        $view = collect($group['permissions'])->firstWhere('value', 'view blog entries');
        $edit = collect($view['children'])->firstWhere('value', 'edit blog entries');
        $publish = collect($edit['children'])->firstWhere('value', 'publish blog entries');

        $this->assertEquals(__('statamic::permissions.publish_{collection}_entries_desc'), $publish['description']);
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
