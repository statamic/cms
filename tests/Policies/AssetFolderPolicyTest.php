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
    public function it_can_be_created($hasPermission, $expected)
    {
        $user = $this->userWithPermissions(
            $hasPermission ? ['edit alfa folders'] : []
        );

        $container = tap(AssetContainer::make('alfa'))->save();

        $this->assertEquals($expected, $user->can('create', [AssetFolder::class, $container]));
    }

    #[Test]
    #[DataProvider('moveProvider')]
    public function it_can_be_moved($folder, $asset, $expected)
    {
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
    #[DataProvider('renameProvider')]
    public function it_can_be_renamed($folder, $asset, $expected)
    {
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
    #[DataProvider('deleteProvider')]
    public function it_can_be_deleted($folder, $asset, $expected)
    {
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
