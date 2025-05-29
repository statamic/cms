<?php

namespace Tests\Feature\AssetContainers;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ListAssetContainersTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_lists_containers_you_have_access_to_when_requested_as_json()
    {
        $this->setTestRoles(['test' => ['access cp', 'view two assets', 'view three assets']]);
        $user = User::make()->assignRole('test')->save();
        AssetContainer::make('one')->save();
        AssetContainer::make('two')->save();
        AssetContainer::make('three')->save();

        $this
            ->actingAs($user)
            ->getJson(cp_route('asset-containers.index'))
            ->assertSuccessful()
            ->assertJson($this->containerArray());
    }

    public function containerArray()
    {
        return [
            [
                'id' => 'three',
                'title' => 'Three',
                'allow_downloading' => true,
                'allow_moving' => true,
                'allow_renaming' => true,
                'allow_uploads' => true,
                'create_folders' => true,
                'edit_url' => 'http://localhost/cp/asset-containers/three/edit',
                'delete_url' => 'http://localhost/cp/asset-containers/three',
                'blueprint_url' => 'http://localhost/cp/asset-containers/three/blueprint',
                'can_edit' => false,
                'can_delete' => false,
            ],
            [
                'id' => 'two',
                'title' => 'Two',
                'allow_downloading' => true,
                'allow_moving' => true,
                'allow_renaming' => true,
                'allow_uploads' => true,
                'create_folders' => true,
                'edit_url' => 'http://localhost/cp/asset-containers/two/edit',
                'delete_url' => 'http://localhost/cp/asset-containers/two',
                'blueprint_url' => 'http://localhost/cp/asset-containers/two/blueprint',
                'can_edit' => false,
                'can_delete' => false,
            ],
        ];
    }
}
