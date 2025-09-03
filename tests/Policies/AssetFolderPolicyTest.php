<?php

namespace Policies;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use Statamic\Contracts\Assets\AssetFolder;
use Statamic\Facades\AssetContainer;
use Tests\Policies\PolicyTestCase;

class AssetFolderPolicyTest extends PolicyTestCase
{
    #[Test]
    #[TestWith([true, true], 'with permission')]
    #[TestWith([false, false], 'without permission')]
    public function it_can_be_created($hasPermission, $expected)
    {
        config(['statamic.assets.v6_permissions' => true]);

        $user = $this->userWithPermissions(
            $hasPermission ? ['edit alfa folders'] : []
        );

        $container = tap(AssetContainer::make('alfa'))->save();

        $this->assertEquals($expected, $user->can('create', [AssetFolder::class, $container]));
    }

    #[Test]
    #[DataProvider('createLegacyProvider')]
    public function it_can_be_created_legacy($hasPermission, $createFolders, $expected)
    {
        // the legacy way is to check for asset upload permission and assetContainer->createFolders()

        $user = $this->userWithPermissions(
            $hasPermission ? ['upload alfa assets'] : []
        );

        $container = tap(AssetContainer::make('alfa')->createFolders($createFolders))->save();

        $this->assertEquals($expected, $user->can('create', [AssetFolder::class, $container]));
    }

    public static function createLegacyProvider()
    {
        return [
            'with permission, can create' => [true, true, true],
            'with permission, cannot create' => [true, false, false],
            'without permission, can create' => [false, true, false],
            'without permission, cannot create' => [false, false, false],
        ];
    }

    #[Test]
    #[DataProvider('moveProvider')]
    public function it_can_be_moved($folder, $asset, $expected)
    {
        config(['statamic.assets.v6_permissions' => true]);

        $user = $this->userWithPermissions(collect([
            $folder ? 'edit alfa folders' : null,
            $asset ? 'move alfa assets' : null,
        ])->filter()->all());
        $container = tap(AssetContainer::make('alfa'))->save();

        $this->assertEquals($expected, $user->can('move', $container->assetFolder('path/to/folder')));
    }

    public static function moveProvider()
    {
        return [
            'folder, asset' => ['folder' => true, 'asset' => true, 'expected' => true],
            'folder only' => ['folder' => true, 'asset' => false, 'expected' => false],
            'asset only' => ['folder' => false, 'asset' => true, 'expected' => false],
            'none' => ['folder' => false, 'asset' => false, 'expected' => false],
        ];
    }

    #[Test]
    #[DataProvider('moveLegacyProvider')]
    public function it_can_be_moved_legacy($hasPermission, $allowMoving, $expected)
    {
        // the legacy way is to check for asset move permission and assetContainer->allowMoving()

        $user = $this->userWithPermissions(
            $hasPermission ? ['move alfa assets'] : []
        );

        $container = tap(AssetContainer::make('alfa')->allowMoving($allowMoving))->save();

        $this->assertEquals($expected, $user->can('move', $container->assetFolder('path/to/folder')));
    }

    public static function moveLegacyProvider()
    {
        return [
            'with permission, can move' => [true, true, true],
            'with permission, cannot move' => [true, false, false],
            'without permission, can move' => [false, true, false],
            'without permission, cannot move' => [false, false, false],
        ];
    }

    #[Test]
    #[DataProvider('renameProvider')]
    public function it_can_be_renamed($folder, $asset, $expected)
    {
        config(['statamic.assets.v6_permissions' => true]);

        $user = $this->userWithPermissions(collect([
            $folder ? 'edit alfa folders' : null,
            $asset ? 'rename alfa assets' : null,
        ])->filter()->all());
        $container = tap(AssetContainer::make('alfa'))->save();

        $this->assertEquals($expected, $user->can('rename', $container->assetFolder('path/to/folder')));
    }

    public static function renameProvider()
    {
        return [
            'folder, asset' => ['folder' => true, 'asset' => true, 'expected' => true],
            'folder only' => ['folder' => true, 'asset' => false, 'expected' => false],
            'asset only' => ['folder' => false, 'asset' => true, 'expected' => false],
            'none' => ['folder' => false, 'asset' => false, 'expected' => false],
        ];
    }

    #[Test]
    #[DataProvider('renameLegacyProvider')]
    public function it_can_be_renamed_legacy()
    {
        // the legacy way is to check for asset rename permission and assetContainer->allowRenaming()

        $user = $this->userWithPermissions(
            ['rename alfa assets']
        );

        $container = tap(AssetContainer::make('alfa')->allowRenaming(true))->save();

        $this->assertTrue($user->can('rename', $container->assetFolder('path/to/folder')));
    }

    public static function renameLegacyProvider()
    {
        return [
            'with permission, can rename' => [true, true, true],
            'with permission, cannot rename' => [true, false, false],
            'without permission, can rename' => [false, true, false],
            'without permission, cannot rename' => [false, false, false],
        ];
    }

    #[Test]
    #[DataProvider('deleteProvider')]
    public function it_can_be_deleted($folder, $asset, $expected)
    {
        config(['statamic.assets.v6_permissions' => true]);

        $user = $this->userWithPermissions(collect([
            $folder ? 'edit alfa folders' : null,
            $asset ? 'delete alfa assets' : null,
        ])->filter()->all());
        $container = tap(AssetContainer::make('alfa'))->save();

        $this->assertEquals($expected, $user->can('delete', $container->assetFolder('path/to/folder')));
    }

    public static function deleteProvider()
    {
        return [
            'folder, asset' => ['folder' => true, 'asset' => true, 'expected' => true],
            'folder only' => ['folder' => true, 'asset' => false, 'expected' => false],
            'asset only' => ['folder' => false, 'asset' => true, 'expected' => false],
            'none' => ['folder' => false, 'asset' => false, 'expected' => false],
        ];
    }

    #[Test]
    #[TestWith([true, true], 'with permission')]
    #[TestWith([false, false], 'without permission')]
    public function it_can_be_deleted_legacy($hasPermission, $expected)
    {
        // the legacy way is to check for asset delete permission

        $user = $this->userWithPermissions(
            $hasPermission ? ['delete alfa assets'] : []
        );

        $container = tap(AssetContainer::make('alfa'))->save();

        $this->assertEquals($expected, $user->can('delete', $container->assetFolder('path/to/folder')));
    }
}
