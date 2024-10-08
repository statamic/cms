<?php

namespace Tests\Policies;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Taxonomies\Taxonomy as TaxonomyContract;
use Statamic\Facades\Taxonomy;

class TaxonomyPolicyTest extends PolicyTestCase
{
    #[Test]
    public function index_is_allowed_if_any_taxonomy_is_viewable()
    {
        $userWithAlfaPermission = $this->userWithPermissions(['view alfa terms']);
        $userWithBravoPermission = $this->userWithPermissions(['view bravo terms']);
        $userWithConfigurePermission = $this->userWithPermissions(['configure taxonomies']);
        $userWithoutPermission = $this->userWithPermissions([]);

        Taxonomy::make('alfa')->save();
        Taxonomy::make('bravo')->save();

        $this->assertTrue($userWithAlfaPermission->can('index', TaxonomyContract::class));
        $this->assertTrue($userWithBravoPermission->can('index', TaxonomyContract::class));
        $this->assertTrue($userWithConfigurePermission->can('index', TaxonomyContract::class));
        $this->assertFalse($userWithoutPermission->can('index', TaxonomyContract::class));
    }

    #[Test]
    public function index_is_allowed_if_any_taxonomy_is_viewable_with_site_permissions()
    {
        $this->withSites(['en', 'fr', 'de']);

        $userWithEnPermission = $this->userWithPermissions([
            'view test terms',
            'access en site',
        ]);
        $userWithDePermission = $this->userWithPermissions([
            'view test terms',
            'access de site',
        ]);

        Taxonomy::make('test')->sites(['en', 'fr'])->save();

        $this->assertTrue($userWithEnPermission->can('index', TaxonomyContract::class));
        $this->assertFalse($userWithDePermission->can('index', TaxonomyContract::class));
    }

    #[Test]
    public function taxonomies_are_viewable_with_view_permissions()
    {
        $user = $this->userWithPermissions(['view alfa terms']);

        $taxonomyA = Taxonomy::make('alfa');
        $taxonomyB = Taxonomy::make('bravo');

        $this->assertTrue($user->can('view', $taxonomyA));
        $this->assertFalse($user->can('edit', $taxonomyA));
        $this->assertFalse($user->can('view', $taxonomyB));
        $this->assertFalse($user->can('edit', $taxonomyB));
    }

    #[Test]
    public function taxonomies_are_editable_with_configure_permissions()
    {
        $authorizedUser = $this->userWithPermissions(['configure taxonomies']);
        $forbiddenUser = $this->userWithPermissions(['edit test terms']);

        $taxonomy = Taxonomy::make('test');

        $this->assertTrue($authorizedUser->can('edit', $taxonomy));
        $this->assertFalse($forbiddenUser->can('edit', $taxonomy));
    }

    #[Test]
    public function taxonomies_can_be_created_with_configure_permission()
    {
        $forbiddenUser = $this->userWithPermissions([]);
        $authorizedUser = $this->userWithPermissions(['configure taxonomies']);

        $this->assertTrue($authorizedUser->can('create', TaxonomyContract::class));
        $this->assertFalse($forbiddenUser->can('create', TaxonomyContract::class));
    }

    #[Test]
    public function taxonomies_can_be_deleted_with_configure_permission()
    {
        $forbiddenUser = $this->userWithPermissions([]);
        $authorizedUser = $this->userWithPermissions(['configure taxonomies']);
        $taxonomy = Taxonomy::make('test');

        $this->assertTrue($authorizedUser->can('delete', $taxonomy));
        $this->assertFalse($forbiddenUser->can('delete', $taxonomy));
    }
}
