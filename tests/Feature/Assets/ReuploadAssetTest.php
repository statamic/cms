<?php

namespace Tests\Feature\Assets;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Actions\ReuploadAsset as ReuploadAssetAction;
use Statamic\Assets\ReplacementFile;
use Statamic\Contracts\Assets\Asset;
use Statamic\Events\AssetReuploaded;
use Statamic\Exceptions\FileExtensionMismatch;
use Statamic\Exceptions\ValidationException;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Glide;
use Statamic\Imaging\PresetGenerator;
use Tests\TestCase;

class ReuploadAssetTest extends TestCase
{
    #[Test]
    public function it_replaces_the_file_when_reuploading()
    {
        // Place an image in the filesystem that would have previously been uploaded using the files fieldtype in the modal.
        $uploadDisk = Storage::fake('local');
        UploadedFile::fake()->image('', 40, 25)->storeAs('statamic/file-uploads/timestamp', 'filename.jpg', ['disk' => 'local']);
        $uploadDisk->assertExists('statamic/file-uploads/timestamp/filename.jpg');

        $asset = Mockery::mock(Asset::class);
        $asset->shouldReceive('id')->andReturn('container::path/to/asset.jpg');
        $asset->shouldReceive('thumbnailUrl')->andReturn('/thumbnailurl');
        $asset->shouldReceive('absoluteUrl')->andReturn('/absoluteurl');
        $asset->shouldReceive('reupload')->withArgs(function ($arg) {
            return $arg instanceof ReplacementFile && $arg->path() === 'statamic/file-uploads/timestamp/filename.jpg';
        })->once();

        $response = (new ReuploadAssetAction)->run(collect([$asset]), ['file' => 'timestamp/filename.jpg']);

        $this->assertEquals([
            'callback' => ['bustAndReloadImageCaches', ['/thumbnailurl', '/absoluteurl']],
            'ids' => ['container::path/to/asset.jpg'],
        ], $response);
    }

    #[Test]
    public function validation_fails_when_attempting_to_replace_with_a_different_file_extension()
    {
        $asset = Mockery::mock(Asset::class);
        $asset->shouldReceive('extension')->andReturn('jpg');
        $asset->shouldReceive('reupload')->withArgs(function ($arg) {
            return $arg instanceof ReplacementFile && $arg->path() === 'statamic/file-uploads/timestamp/filename.png';
        })->once()->andThrow(new FileExtensionMismatch);

        try {
            (new ReuploadAssetAction)->run(collect([$asset]), ['file' => 'timestamp/filename.png']);
        } catch (ValidationException $e) {
            $this->assertEquals('Must be a file of type: jpg.', $e->errors()['file'][0]);

            return;
        }

        $this->fail('Validation exception was not thrown.');
    }

    #[Test]
    public function glide_cache_is_cleared_and_presets_are_regenerated_when_reuploading()
    {
        Storage::fake('test');
        $container = AssetContainer::make('test_container')->disk('test');
        AssetContainer::shouldReceive('find')->with('test_container')->andReturn($container);
        $asset = $container->makeAsset('test.jpg');

        // The order matters, otherwise if the glide cache is cleared after generating presets, it was a waste.
        Glide::shouldReceive('clearAsset')->withArgs(fn ($arg1) => $arg1->id() === $asset->id())->once()->globally()->ordered();
        $this->mock(PresetGenerator::class)->shouldReceive('generate')->withArgs(fn ($arg1) => $arg1->id() === $asset->id())->once()->globally()->ordered();

        AssetReuploaded::dispatch($asset);
    }
}
