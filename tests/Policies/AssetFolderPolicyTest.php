<?php

namespace Tests\Policies;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use Statamic\Contracts\Assets\AssetFolder;
use Statamic\Facades\AssetContainer;

class AssetFolderPolicyTest extends PolicyTestCase
{
    #[Test]
    #[TestWith([true, true], 'with permission')]
    #[TestWith([false, false], 'without permission')]
    public function it_can_be_created_v6($hasPermission, $expected)
    {
        // The v6 way doesn't need to check the container for createFolders() because it'll be removed.
        config(['statamic.assets.v6_permissions' => true]);

        $user = $this->userWithPermissions(
            $hasPermission ? ['edit alfa folders'] : []
        );

        $container = tap(AssetContainer::make('alfa'))->save();

        $this->assertEquals($expected, $user->can('create', [AssetFolder::class, $container]));
    }

    #[Test]
    #[DataProvider('createProvider')]
    public function it_can_be_created($hasPermission, $createFolders, $expected)
    {
        $user = $this->userWithPermissions(
            $hasPermission ? ['upload alfa assets'] : []
        );

        $container = tap(AssetContainer::make('alfa')->createFolders($createFolders))->save();

        $this->assertEquals($expected, $user->can('create', [AssetFolder::class, $container]));
    }

    public static function createProvider()
    {
        return [
            'with permission, can create' => [true, true, true],
            'with permission, cannot create' => [true, false, false],
            'without permission, can create' => [false, true, false],
            'without permission, cannot create' => [false, false, false],
        ];
    }

    #[Test]
    #[DataProvider('moveV6Provider')]
    public function it_can_be_moved_v6($folder, $asset, $expected)
    {
        // The v6 way doesn't need to check the container for allowMoving() because it'll be removed.
        config(['statamic.assets.v6_permissions' => true]);

        $user = $this->userWithPermissions(collect([
            $folder ? 'edit alfa folders' : null,
            $asset ? 'move alfa assets' : null,
        ])->filter()->all());
        $container = tap(AssetContainer::make('alfa'))->save();

        $this->assertEquals($expected, $user->can('move', $container->assetFolder('path/to/folder')));
    }

    public static function moveV6Provider()
    {
        return [
            'folder, asset' => ['folder' => true, 'asset' => true, 'expected' => true],
            'folder only' => ['folder' => true, 'asset' => false, 'expected' => false],
            'asset only' => ['folder' => false, 'asset' => true, 'expected' => false],
            'none' => ['folder' => false, 'asset' => false, 'expected' => false],
        ];
    }

    #[Test]
    #[DataProvider('moveProvider')]
    public function it_can_be_moved($hasPermission, $allowMoving, $expected)
    {
        $user = $this->userWithPermissions(
            $hasPermission ? ['move alfa assets'] : []
        );

        $container = tap(AssetContainer::make('alfa')->allowMoving($allowMoving))->save();

        $this->assertEquals($expected, $user->can('move', $container->assetFolder('path/to/folder')));
    }

    public static function moveProvider()
    {
        return [
            'with permission, can move' => [true, true, true],
            'with permission, cannot move' => [true, false, false],
            'without permission, can move' => [false, true, false],
            'without permission, cannot move' => [false, false, false],
        ];
    }

    #[Test]
    #[DataProvider('renameV6Provider')]
    public function it_can_be_renamed_v6($folder, $asset, $expected)
    {
        // The v6 way doesn't need to check the container for allowRenaming() because it'll be removed.
        config(['statamic.assets.v6_permissions' => true]);

        $user = $this->userWithPermissions(collect([
            $folder ? 'edit alfa folders' : null,
            $asset ? 'rename alfa assets' : null,
        ])->filter()->all());
        $container = tap(AssetContainer::make('alfa'))->save();

        $this->assertEquals($expected, $user->can('rename', $container->assetFolder('path/to/folder')));
    }

    public static function renameV6Provider()
    {
        return [
            'folder, asset' => ['folder' => true, 'asset' => true, 'expected' => true],
            'folder only' => ['folder' => true, 'asset' => false, 'expected' => false],
            'asset only' => ['folder' => false, 'asset' => true, 'expected' => false],
            'none' => ['folder' => false, 'asset' => false, 'expected' => false],
        ];
    }

    #[Test]
    #[DataProvider('renameProvider')]
    public function it_can_be_renamed()
    {
        $user = $this->userWithPermissions(
            ['rename alfa assets']
        );

        $container = tap(AssetContainer::make('alfa')->allowRenaming(true))->save();

        $this->assertTrue($user->can('rename', $container->assetFolder('path/to/folder')));
    }

    public static function renameProvider()
    {
        return [
            'with permission, can rename' => [true, true, true],
            'with permission, cannot rename' => [true, false, false],
            'without permission, can rename' => [false, true, false],
            'without permission, cannot rename' => [false, false, false],
        ];
    }

    #[Test]
    #[DataProvider('deleteV6Provider')]
    public function it_can_be_deleted_v6($folder, $asset, $expected)
    {
        // the v6 way checks for both folder and asset delete permissions.
        config(['statamic.assets.v6_permissions' => true]);

        $user = $this->userWithPermissions(collect([
            $folder ? 'edit alfa folders' : null,
            $asset ? 'delete alfa assets' : null,
        ])->filter()->all());
        $container = tap(AssetContainer::make('alfa'))->save();

        $this->assertEquals($expected, $user->can('delete', $container->assetFolder('path/to/folder')));
    }

    public static function deleteV6Provider()
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
    public function it_can_be_deleted($hasPermission, $expected)
    {
        // the legacy way is to check for asset delete permission

        $user = $this->userWithPermissions(
            $hasPermission ? ['delete alfa assets'] : []
        );

        $container = tap(AssetContainer::make('alfa'))->save();

        $this->assertEquals($expected, $user->can('delete', $container->assetFolder('path/to/folder')));
    }

    #[Test]
    public function user_with_configure_permission_can_do_it_all()
    {
        $userWithPermission = $this->userWithPermissions(['configure asset containers']);
        $userWithoutPermission = $this->userWithPermissions([]);

        $container = tap(AssetContainer::make('alfa'))->save();
        $folder = $container->assetFolder('path/to/folder');

        $this->assertTrue($userWithPermission->can('create', [AssetFolder::class, $container]));
        $this->assertTrue($userWithPermission->can('move', $folder));
        $this->assertTrue($userWithPermission->can('rename', $folder));
        $this->assertTrue($userWithPermission->can('delete', $folder));

        $this->assertFalse($userWithoutPermission->can('create', [AssetFolder::class, $container]));
        $this->assertFalse($userWithoutPermission->can('move', $folder));
        $this->assertFalse($userWithoutPermission->can('rename', $folder));
        $this->assertFalse($userWithoutPermission->can('delete', $folder));
    }
}
