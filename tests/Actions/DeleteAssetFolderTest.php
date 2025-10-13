<?php

namespace Tests\Actions;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Assets\AssetContainer;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class DeleteAssetFolderTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    private $container;

    public function setUp(): void
    {
        parent::setUp();

        Storage::fake('test');

        $this->container = tap(
            (new AssetContainer)->handle('test_container')->disk('test')
        )->save();
    }

    private function createAsset($filename)
    {
        $file = UploadedFile::fake()->image($filename, 30, 60);
        Storage::disk('test')->putFileAs($file, $filename);
    }

    private function assertAssetExists($file)
    {
        Storage::disk('test')->assertExists($file);
        $this->assertNotNull($this->container->asset($file));
    }

    private function assertAssetDoesNotExist($file)
    {
        Storage::disk('test')->assertMissing($file);
        $this->assertNull($this->container->asset($file));
    }

    private function deleteFolder($folder)
    {
        return $this->post(cp_route('assets.folders.actions.run', ['asset_container' => 'test_container']), [
            'action' => 'delete',
            'context' => ['container' => 'test_container'],
            'selections' => [$folder],
            'values' => [],
        ]);
    }

    #[Test]
    public function it_deletes()
    {
        $this->createAsset('foo/alfa.jpg');
        $this->createAsset('foo/bravo.jpg');
        $this->createAsset('bar/charlie.jpg');
        $this->createAsset('delta.jpg');
        Storage::disk('test')->assertExists('foo');

        $this
            ->actingAs(tap(User::make()->makeSuper())->save())
            ->deleteFolder('foo')
            ->assertOk();

        Storage::disk('test')->assertMissing('foo');
        $this->assertAssetDoesNotExist('foo/alfa.jpg');
        $this->assertAssetDoesNotExist('foo/bravo.jpg');
        $this->assertAssetExists('bar/charlie.jpg');
        $this->assertAssetExists('delta.jpg');
    }

    #[Test]
    public function no_path_traversal()
    {
        $this->createAsset('foo/alfa.jpg');
        $this->createAsset('foo/bravo.jpg');
        $this->createAsset('bar/charlie.jpg');
        $this->createAsset('delta.jpg');
        Storage::disk('test')->assertExists('foo');

        $this
            ->actingAs(tap(User::make()->makeSuper())->save())
            ->deleteFolder('foo/..')
            ->assertSessionHasErrors(['selections' => 'Path traversal detected: foo/..']);

        Storage::disk('test')->assertExists('foo');
        $this->assertAssetExists('foo/alfa.jpg');
        $this->assertAssetExists('foo/bravo.jpg');
        $this->assertAssetExists('bar/charlie.jpg');
        $this->assertAssetExists('delta.jpg');
    }
}
