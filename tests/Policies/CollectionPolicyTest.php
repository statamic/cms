<?php

namespace Tests\Policies;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Entries\Collection as CollectionContract;
use Statamic\Facades\Collection;

class CollectionPolicyTest extends PolicyTestCase
{
    #[Test]
    public function index_is_allowed_if_any_collection_is_viewable()
    {
        $userWithAlfaPermission = $this->userWithPermissions(['view alfa entries']);
        $userWithBravoPermission = $this->userWithPermissions(['view bravo entries']);
        $userWithConfigurePermission = $this->userWithPermissions(['configure collections']);
        $userWithoutPermission = $this->userWithPermissions([]);

        Collection::make('alfa')->save();
        Collection::make('bravo')->save();

        $this->assertTrue($userWithAlfaPermission->can('index', CollectionContract::class));
        $this->assertTrue($userWithBravoPermission->can('index', CollectionContract::class));
        $this->assertTrue($userWithConfigurePermission->can('index', CollectionContract::class));
        $this->assertFalse($userWithoutPermission->can('index', CollectionContract::class));
    }

    #[Test]
    public function index_is_allowed_if_any_collection_is_viewable_with_site_permissions()
    {
        $this->withSites(['en', 'fr', 'de']);

        $userWithEnPermission = $this->userWithPermissions([
            'view test entries',
            'access en site',
        ]);
        $userWithDePermission = $this->userWithPermissions([
            'view test entries',
            'access de site',
        ]);

        Collection::make('test')->sites(['en', 'fr'])->save();

        $this->assertTrue($userWithEnPermission->can('index', CollectionContract::class));
        $this->assertFalse($userWithDePermission->can('index', CollectionContract::class));
    }

    #[Test]
    public function collections_are_viewable_with_view_permissions()
    {
        $user = $this->userWithPermissions(['view alfa entries']);

        $collectionA = Collection::make('alfa');
        $collectionB = Collection::make('bravo');

        $this->assertTrue($user->can('view', $collectionA));
        $this->assertFalse($user->can('edit', $collectionA));
        $this->assertFalse($user->can('view', $collectionB));
        $this->assertFalse($user->can('edit', $collectionB));
    }

    #[Test]
    public function collections_are_editable_with_configure_permissions()
    {
        $authorizedUser = $this->userWithPermissions(['configure collections']);
        $forbiddenUser = $this->userWithPermissions(['edit test entries']);

        $collection = Collection::make('test');

        $this->assertTrue($authorizedUser->can('edit', $collection));
        $this->assertFalse($forbiddenUser->can('edit', $collection));
    }

    #[Test]
    public function collections_can_be_created_with_configure_permission()
    {
        $forbiddenUser = $this->userWithPermissions([]);
        $authorizedUser = $this->userWithPermissions(['configure collections']);

        $this->assertTrue($authorizedUser->can('create', CollectionContract::class));
        $this->assertFalse($forbiddenUser->can('create', CollectionContract::class));
    }

    #[Test]
    public function collections_can_be_deleted_with_configure_permission()
    {
        $forbiddenUser = $this->userWithPermissions([]);
        $authorizedUser = $this->userWithPermissions(['configure collections']);
        $collection = Collection::make('test');

        $this->assertTrue($authorizedUser->can('delete', $collection));
        $this->assertFalse($forbiddenUser->can('delete', $collection));
    }
}
