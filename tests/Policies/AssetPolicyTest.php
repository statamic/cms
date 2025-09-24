<?php

namespace Tests\Policies;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Assets\Asset;
use Statamic\Facades\AssetContainer;

class AssetPolicyTest extends PolicyTestCase
{
    #[Test]
    public function it_can_be_viewed()
    {
        $user = $this->userWithPermissions(['view alfa assets']);
        $containerA = tap(AssetContainer::make('alfa'))->save();
        $containerB = tap(AssetContainer::make('bravo'))->save();

        $this->assertTrue($user->can('view', $containerA->makeAsset('test.txt')));
        $this->assertFalse($user->can('view', $containerB->makeAsset('test.txt')));
    }

    #[Test]
    public function it_can_be_edited()
    {
        $user = $this->userWithPermissions(['edit alfa assets']);
        $containerA = tap(AssetContainer::make('alfa'))->save();
        $containerB = tap(AssetContainer::make('bravo'))->save();

        $this->assertTrue($user->can('edit', $containerA->makeAsset('test.txt')));
        $this->assertFalse($user->can('edit', $containerB->makeAsset('test.txt')));
    }

    #[Test]
    public function it_can_be_stored_v6()
    {
        // The v6 way doesn't need to check the container for allowUploads() because it'll be removed.
        config(['statamic.assets.v6_permissions' => true]);

        $user = $this->userWithPermissions(['upload alfa assets']);
        $containerA = tap(AssetContainer::make('alfa'))->save();
        $containerB = tap(AssetContainer::make('bravo'))->save();

        $this->assertTrue($user->can('store', [Asset::class, $containerA]));
        $this->assertFalse($user->can('store', [Asset::class, $containerB]));
    }

    #[Test]
    public function it_can_be_stored()
    {
        $user = $this->userWithPermissions([
            'upload alfa assets',
            'upload charlie assets',
        ]);
        $containerA = tap(AssetContainer::make('alfa'))->save();
        $containerB = tap(AssetContainer::make('bravo'))->save();
        $containerC = tap(AssetContainer::make('charlie')->allowUploads(false))->save();
        $containerD = tap(AssetContainer::make('delta')->allowUploads(false))->save();

        $this->assertTrue($user->can('store', [Asset::class, $containerA]));
        $this->assertFalse($user->can('store', [Asset::class, $containerB]));
        $this->assertFalse($user->can('store', [Asset::class, $containerC]));
        $this->assertFalse($user->can('store', [Asset::class, $containerD]));
    }

    #[Test]
    public function it_can_be_moved_v6()
    {
        // The v6 way doesn't need to check the container for allowMoving() because it'll be removed.
        config(['statamic.assets.v6_permissions' => true]);

        $user = $this->userWithPermissions(['move alfa assets']);
        $containerA = tap(AssetContainer::make('alfa'))->save();
        $containerB = tap(AssetContainer::make('bravo'))->save();

        $this->assertTrue($user->can('move', $containerA->makeAsset('test.txt')));
        $this->assertFalse($user->can('move', $containerB->makeAsset('test.txt')));
    }

    #[Test]
    public function it_can_be_moved()
    {
        $user = $this->userWithPermissions([
            'move alfa assets',
            'move charlie assets',
        ]);
        $containerA = tap(AssetContainer::make('alfa'))->save();
        $containerB = tap(AssetContainer::make('bravo'))->save();
        $containerC = tap(AssetContainer::make('charlie')->allowMoving(false))->save();
        $containerD = tap(AssetContainer::make('delta')->allowMoving(false))->save();

        $this->assertTrue($user->can('move', $containerA->makeAsset('test.txt')));
        $this->assertFalse($user->can('move', $containerB->makeAsset('test.txt')));
        $this->assertFalse($user->can('move', $containerC->makeAsset('test.txt')));
        $this->assertFalse($user->can('move', $containerD->makeAsset('test.txt')));
    }

    #[Test]
    public function it_can_be_renamed_v6()
    {
        // The v6 way doesn't need to check the container for allowRenaming() because it'll be removed.
        config(['statamic.assets.v6_permissions' => true]);

        $user = $this->userWithPermissions(['rename alfa assets']);
        $containerA = tap(AssetContainer::make('alfa'))->save();
        $containerB = tap(AssetContainer::make('bravo'))->save();

        $this->assertTrue($user->can('rename', $containerA->makeAsset('test.txt')));
        $this->assertFalse($user->can('rename', $containerB->makeAsset('test.txt')));
    }

    #[Test]
    public function it_can_be_renamed()
    {
        $user = $this->userWithPermissions([
            'rename alfa assets',
            'rename charlie assets',
        ]);
        $containerA = tap(AssetContainer::make('alfa'))->save();
        $containerB = tap(AssetContainer::make('bravo'))->save();
        $containerC = tap(AssetContainer::make('charlie')->allowRenaming(false))->save();
        $containerD = tap(AssetContainer::make('delta')->allowRenaming(false))->save();

        $this->assertTrue($user->can('rename', $containerA->makeAsset('test.txt')));
        $this->assertFalse($user->can('rename', $containerB->makeAsset('test.txt')));
        $this->assertFalse($user->can('rename', $containerC->makeAsset('test.txt')));
        $this->assertFalse($user->can('rename', $containerD->makeAsset('test.txt')));
    }

    #[Test]
    public function it_can_be_deleted()
    {
        $user = $this->userWithPermissions(['delete alfa assets']);
        $containerA = tap(AssetContainer::make('alfa'))->save();
        $containerB = tap(AssetContainer::make('bravo'))->save();

        $this->assertTrue($user->can('delete', $containerA->makeAsset('test.txt')));
        $this->assertFalse($user->can('delete', $containerB->makeAsset('test.txt')));
    }

    #[Test]
    #[DataProvider('replaceProvider')]
    public function it_can_be_replaced($canEdit, $canStore, $canDelete, $expected)
    {
        $user = $this->userWithPermissions(collect([
            $canEdit ? 'edit alfa assets' : null,
            $canStore ? 'upload alfa assets' : null,
            $canDelete ? 'delete alfa assets' : null,
        ])->filter()->all());
        $container = tap(AssetContainer::make('alfa'))->save();

        $this->assertEquals($expected, $user->can('replace', $container->makeAsset('test.txt')));
    }

    public static function replaceProvider()
    {
        return [
            'edit, store, delete' => ['canEdit' => true, 'canStore' => true, 'canDelete' => true, 'expected' => true],
            'edit, store' => ['canEdit' => true, 'canStore' => true, 'canDelete' => false, 'expected' => false],
            'edit, delete' => ['canEdit' => true, 'canStore' => false, 'canDelete' => true, 'expected' => false],
            'store, delete' => ['canEdit' => false, 'canStore' => true, 'canDelete' => true, 'expected' => false],
            'edit only' => ['canEdit' => true, 'canStore' => false, 'canDelete' => false, 'expected' => false],
            'store only' => ['canEdit' => false, 'canStore' => true, 'canDelete' => false, 'expected' => false],
            'delete only' => ['canEdit' => false, 'canStore' => false, 'canDelete' => true, 'expected' => false],
            'none' => ['canEdit' => false, 'canStore' => false, 'canDelete' => false, 'expected' => false],
        ];
    }

    #[Test]
    #[DataProvider('reuploadProvider')]
    public function it_can_be_reuploaded($canEdit, $canStore, $expected)
    {
        $user = $this->userWithPermissions(collect([
            $canEdit ? 'edit alfa assets' : null,
            $canStore ? 'upload alfa assets' : null,
        ])->filter()->all());
        $container = tap(AssetContainer::make('alfa'))->save();

        $this->assertEquals($expected, $user->can('reupload', $container->makeAsset('test.txt')));
    }

    public static function reuploadProvider()
    {
        return [
            'edit, store' => ['canEdit' => true, 'canStore' => true, 'expected' => true],
            'edit only' => ['canEdit' => true, 'canStore' => false, 'expected' => false],
            'store only' => ['canEdit' => false, 'canStore' => true, 'expected' => false],
            'none' => ['canEdit' => false, 'canStore' => false, 'expected' => false],
        ];
    }

    #[Test]
    public function user_with_configure_permission_can_do_it_all()
    {
        $userWithPermission = $this->userWithPermissions(['configure asset containers']);
        $userWithoutPermission = $this->userWithPermissions([]);

        $container = tap(AssetContainer::make('alfa'))->save();
        $asset = $container->makeAsset('test.txt');

        $this->assertTrue($userWithPermission->can('view', $asset));
        $this->assertTrue($userWithPermission->can('edit', $asset));
        $this->assertTrue($userWithPermission->can('store', [Asset::class, $container]));
        $this->assertTrue($userWithPermission->can('move', $asset));
        $this->assertTrue($userWithPermission->can('rename', $asset));
        $this->assertTrue($userWithPermission->can('delete', $asset));
        $this->assertTrue($userWithPermission->can('replace', $asset));
        $this->assertTrue($userWithPermission->can('reupload', $asset));

        $this->assertFalse($userWithoutPermission->can('view', $asset));
        $this->assertFalse($userWithoutPermission->can('edit', $asset));
        $this->assertFalse($userWithoutPermission->can('store', [Asset::class, $container]));
        $this->assertFalse($userWithoutPermission->can('move', $asset));
        $this->assertFalse($userWithoutPermission->can('rename', $asset));
        $this->assertFalse($userWithoutPermission->can('delete', $asset));
        $this->assertFalse($userWithoutPermission->can('replace', $asset));
        $this->assertFalse($userWithoutPermission->can('reupload', $asset));
    }
}
