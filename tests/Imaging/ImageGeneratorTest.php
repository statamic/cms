<?php

namespace Tests\Imaging;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use League\Glide\Server;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\File;
use Statamic\Imaging\ImageGenerator;
use Statamic\Support\Str;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ImageGeneratorTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        $this->clearGlideCache();
    }

    /** @test */
    public function it_generates_an_image_by_asset()
    {
        Storage::fake('test');
        $file = UploadedFile::fake()->image('foo/hoff.jpg', 30, 60);
        Storage::disk('test')->putFileAs('foo', $file, 'hoff.jpg');
        $container = tap(AssetContainer::make('test_container')->disk('test'))->save();
        $asset = tap($container->makeAsset('foo/hoff.jpg'))->save();

        $this->assertCount(0, $this->generatedImagePaths());

        $path = $this->makeGenerator()->generateByAsset(
            $asset,
            $userParams = ['w' => 100, 'h' => 100]
        );

        // Since we can't really mock the server, we'll generate the md5 hash the same
        // way it does. It will also include the fit parameter based on the asset's
        // focal point since it does it automatically via our "auto_crop" setting.
        $actualParams = array_merge($userParams, ['fit' => 'crop-50-50']);
        $md5 = $this->getGlideMd5($asset->basename(), $actualParams);

        $expectedPath = "containers/test_container/foo/hoff.jpg/{$md5}.jpg";

        $this->assertEquals($expectedPath, $path);
        $this->assertCount(1, $paths = $this->generatedImagePaths());
        $this->assertContains($expectedPath, $paths);
    }

    /** @test */
    public function it_generates_an_image_by_local_path()
    {
        $this->assertCount(0, $this->generatedImagePaths());

        // Path relative to the "public" directory.
        $imagePath = 'testimages/foo/hoff.jpg';

        $image = UploadedFile::fake()->image('', 30, 60);
        File::put(public_path($imagePath), $image->getContent());

        $path = $this->makeGenerator()->generateByPath(
            $imagePath,
            $userParams = ['w' => 100, 'h' => 100]
        );

        // Since we can't really mock the server, we'll generate the md5 hash the same
        // way it does. It will not include the fit parameter since it's not an asset.
        $md5 = $this->getGlideMd5($imagePath, $userParams);

        $expectedPath = "paths/testimages/foo/hoff.jpg/{$md5}.jpg";

        $this->assertEquals($expectedPath, $path);
        $this->assertCount(1, $paths = $this->generatedImagePaths());
        $this->assertContains($expectedPath, $paths);
    }

    /** @test */
    public function it_generates_an_image_by_url()
    {
    }

    private function makeGenerator()
    {
        return new ImageGenerator($this->app->make(Server::class));
    }

    private function clearGlideCache()
    {
        File::delete($this->glideCachePath());
    }

    private function glideCachePath()
    {
        return 'storage/statamic/glide';
    }

    private function generatedImagePaths()
    {
        return File::getFilesRecursively($this->glideCachePath())
            ->map(fn ($path) => (string) Str::of($path)->after($this->glideCachePath().'/'))
            ->all();
    }

    private function getGlideMd5($basename, $params)
    {
        ksort($params);

        return md5($basename.'?'.http_build_query($params));
    }
}
