<?php

namespace Tests\Actions;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Assets\AssetContainer;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ReplaceAssetTest extends TestCase
{
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

    #[Test]
    public function it_replaces_an_asset()
    {
        $this->createAsset('alfa.jpg');
        $this->createAsset('bravo.jpg');

        $this
            ->actingAs(tap(User::make()->makeSuper())->save())
            ->replace('alfa.jpg', 'bravo.jpg')
            ->assertOk();

        Storage::disk('test')->assertMissing('path/to/alfa.jpg');
        Storage::disk('test')->assertExists('path/to/bravo.jpg');
    }

    #[Test]
    public function it_404s_when_the_selected_asset_does_not_exist()
    {
        $this->createAsset('bravo.jpg');

        $this
            ->actingAs(tap(User::make()->makeSuper())->save())
            ->replace('does-not-exist.jpg', 'bravo.jpg')
            ->assertNotFound();
    }

    #[Test]
    public function it_fails_validation_when_replacing_an_asset_with_itself()
    {
        $this->createAsset('alfa.jpg');

        $this
            ->actingAs(tap(User::make()->makeSuper())->save())
            ->replace('alfa.jpg', 'alfa.jpg')
            ->assertSessionHasErrors(['new_asset' => trans('statamic::validation.asset_replace_same')]);

        Storage::disk('test')->assertExists('path/to/alfa.jpg');
        $this->assertNotNull($this->container->asset('path/to/alfa.jpg'));
    }

    private function createAsset($filename)
    {
        Storage::disk('test')->putFileAs('path/to', UploadedFile::fake()->image($filename, 30, 60), $filename);
    }

    private function replace($file, $newFile)
    {
        return $this->post(cp_route('assets.actions.run'), [
            'action' => 'replace_asset',
            'selections' => ['test_container::path/to/'.$file],
            'context' => ['container' => 'test_container', 'folder' => 'path/to'],
            'values' => ['new_asset' => ['test_container::path/to/'.$newFile], 'delete_original' => true],
        ]);
    }
}
