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

class RenameAssetTest extends TestCase
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

    private function createAsset($filename, $alt)
    {
        $file = UploadedFile::fake()->image($filename, 30, 60);
        Storage::disk('test')->putFileAs('path/to', $file, $filename);
        Storage::disk('test')->put('path/to/.meta/'.$filename.'.yaml', "data:\n  alt: '".$alt."'");
    }

    private function assertAssetExistsAndHasData($file, $data)
    {
        Storage::disk('test')->assertExists('path/to/'.$file);
        $this->assertNotNull($asset = $this->container->asset('path/to/'.$file));
        $this->assertEquals($data, $asset->data()->all());
    }

    private function assertAssetDoesNotExist($file)
    {
        Storage::disk('test')->assertMissing($file);
        $this->assertNull($this->container->asset($file));
    }

    private function rename($file, $newFilename)
    {
        return $this->post(cp_route('assets.actions.run'), [
            'action' => 'rename_asset',
            'selections' => ['test_container::path/to/'.$file],
            'values' => ['filename' => $newFilename],
        ]);
    }

    #[Test]
    public function it_renames()
    {
        $this->createAsset('alfa.jpg', 'The alfa alt text');

        $this
            ->actingAs(tap(User::make()->makeSuper())->save())
            ->rename('alfa.jpg', 'bravo')
            ->assertOk();

        $this->assertAssetDoesNotExist('alfa.jpg');
        $this->assertAssetExistsAndHasData('bravo.jpg', ['alt' => 'The alfa alt text']);
    }

    #[Test]
    public function it_fails_validation_if_provided_with_the_current_filename()
    {
        $this->createAsset('alfa.jpg', 'The alfa alt text');

        $this
            ->actingAs(tap(User::make()->makeSuper())->save())
            ->rename('alfa.jpg', 'alfa')
            ->assertSessionHasErrors(['filename' => trans('statamic::validation.asset_current_filename')]);
    }

    #[Test]
    public function it_fails_validation_if_provided_with_string_that_resolves_to_current_filename()
    {
        $this->createAsset('alfa-one.jpg', 'The alfa alt text');

        $this
            ->actingAs(tap(User::make()->makeSuper())->save())
            ->rename('alfa-one.jpg', 'AlFa OnE') // "AlFa OnE" would resolve to "alfa-one"
            ->assertSessionHasErrors(['filename' => trans('statamic::validation.asset_current_filename')]);
    }

    #[Test]
    public function it_fails_validation_if_provided_with_existing_filename()
    {
        $this->createAsset('alfa.jpg', 'The alfa alt text');
        $this->createAsset('bravo.jpg', 'The bravo alt text');

        $this
            ->actingAs(tap(User::make()->makeSuper())->save())
            ->rename('alfa.jpg', 'bravo')
            ->assertSessionHasErrors(['filename' => trans('statamic::validation.asset_file_exists_same_content')]);
    }

    #[Test]
    public function it_fails_validation_if_provided_with_string_that_resolves_to_existing_filename()
    {
        $this->createAsset('alfa.jpg', 'The alfa alt text');
        $this->createAsset('bravo-two.jpg', 'The bravo alt text');

        $this
            ->actingAs(tap(User::make()->makeSuper())->save())
            ->rename('alfa.jpg', 'BraVo TwO') // "BraVo TwO" would resolve to "bravo-two"
            ->assertSessionHasErrors(['filename' => trans('statamic::validation.asset_file_exists_same_content')]);
    }
}
