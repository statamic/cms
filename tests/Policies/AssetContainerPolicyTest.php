<?php

namespace Policies;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Assets\Asset;
use Statamic\Facades\AssetContainer;
use Tests\Policies\PolicyTestCase;

class AssetContainerPolicyTest extends PolicyTestCase
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
    public function it_can_be_stored()
    {
        $user = $this->userWithPermissions(['upload alfa assets']);
        $containerA = tap(AssetContainer::make('alfa'))->save();
        $containerB = tap(AssetContainer::make('bravo'))->save();

        $this->assertTrue($user->can('store', [Asset::class, $containerA]));
        $this->assertFalse($user->can('store', [Asset::class, $containerB]));
    }

    #[Test]
    public function it_can_be_moved()
    {
        $user = $this->userWithPermissions(['move alfa assets']);
        $containerA = tap(AssetContainer::make('alfa'))->save();
        $containerB = tap(AssetContainer::make('bravo'))->save();

        $this->assertTrue($user->can('move', $containerA->makeAsset('test.txt')));
        $this->assertFalse($user->can('move', $containerB->makeAsset('test.txt')));
    }

    #[Test]
    public function it_can_be_renamed()
    {
        $user = $this->userWithPermissions(['rename alfa assets']);
        $containerA = tap(AssetContainer::make('alfa'))->save();
        $containerB = tap(AssetContainer::make('bravo'))->save();

        $this->assertTrue($user->can('rename', $containerA->makeAsset('test.txt')));
        $this->assertFalse($user->can('rename', $containerB->makeAsset('test.txt')));
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
}
