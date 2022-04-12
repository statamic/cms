<?php

namespace Tests\Imaging;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use League\Glide\Server;
use Statamic\Events\GlideImageGenerated;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\File;
use Statamic\Facades\Glide;
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
        Event::fake();

        $cacheKey = 'asset::test_container::foo/hoff.jpg::4dbc41d8e3ba1ccd302641e509b48768';
        $this->assertNull(Glide::cacheStore()->get($cacheKey));

        Storage::fake('test');
        $file = UploadedFile::fake()->image('foo/hoff.jpg', 30, 60);
        Storage::disk('test')->putFileAs('foo', $file, 'hoff.jpg');
        $container = tap(AssetContainer::make('test_container')->disk('test'))->save();
        $asset = tap($container->makeAsset('foo/hoff.jpg'))->save();

        $this->assertCount(0, $this->generatedImagePaths());

        // Generate the image twice to make sure it's cached.
        foreach (range(1, 2) as $i) {
            $path = $this->makeGenerator()->generateByAsset(
                $asset,
                $userParams = ['w' => 100, 'h' => 100]
            );
        }

        // Since we can't really mock the server, we'll generate the md5 hash the same
        // way it does. It will also include the fit parameter based on the asset's
        // focal point since it does it automatically via our "auto_crop" setting.
        $actualParams = array_merge($userParams, ['fit' => 'crop-50-50']);
        $md5 = $this->getGlideMd5($asset->basename(), $actualParams);

        $expectedPath = "containers/test_container/foo/hoff.jpg/{$md5}.jpg";

        $this->assertEquals($expectedPath, $path);
        $this->assertCount(1, $paths = $this->generatedImagePaths());
        $this->assertContains($expectedPath, $paths);
        $this->assertEquals($expectedPath, Glide::cacheStore()->get($cacheKey));
        Event::assertDispatchedTimes(GlideImageGenerated::class, 1);
    }

    /** @test */
    public function it_generates_an_image_by_local_path()
    {
        Event::fake();

        $cacheKey = 'path::testimages/foo/hoff.jpg::4dbc41d8e3ba1ccd302641e509b48768';
        $this->assertNull(Glide::cacheStore()->get($cacheKey));

        $this->assertCount(0, $this->generatedImagePaths());

        // Path relative to the "public" directory.
        $imagePath = 'testimages/foo/hoff.jpg';

        $image = UploadedFile::fake()->image('', 30, 60);
        $contents = file_get_contents($image->getPathname());
        File::put(public_path($imagePath), $contents);

        // Generate the image twice to make sure it's cached.
        foreach (range(1, 2) as $i) {
            $path = $this->makeGenerator()->generateByPath(
                $imagePath,
                $userParams = ['w' => 100, 'h' => 100]
            );
        }

        // Since we can't really mock the server, we'll generate the md5 hash the same
        // way it does. It will not include the fit parameter since it's not an asset.
        $md5 = $this->getGlideMd5($imagePath, $userParams);

        $expectedPath = "paths/testimages/foo/hoff.jpg/{$md5}.jpg";

        $this->assertEquals($expectedPath, $path);
        $this->assertCount(1, $paths = $this->generatedImagePaths());
        $this->assertContains($expectedPath, $paths);
        $this->assertEquals($expectedPath, Glide::cacheStore()->get($cacheKey));
        Event::assertDispatchedTimes(GlideImageGenerated::class, 1);
    }

    /** @test */
    public function it_generates_an_image_by_external_url()
    {
        Event::fake();

        $cacheKey = 'url::https://example.com/foo/hoff.jpg::4dbc41d8e3ba1ccd302641e509b48768';
        $this->assertNull(Glide::cacheStore()->get($cacheKey));

        $this->assertCount(0, $this->generatedImagePaths());

        $this->app->bind('statamic.imaging.guzzle', function () {
            $file = UploadedFile::fake()->image('', 30, 60);
            $contents = file_get_contents($file->getPathname());

            $response = new Response(200, [], $contents);

            // Glide, Flysystem, or the Guzzle adapter will try to perform the requests
            // at different points to check if the file exists or to get the content
            // of it. Here we'll just mock the same response multiple times.
            return new Client(['handler' => new MockHandler([
                $response, $response, $response,
            ])]);
        });

        // Generate the image twice to make sure it's cached.
        foreach (range(1, 2) as $i) {
            $path = $this->makeGenerator()->generateByUrl(
                'https://example.com/foo/hoff.jpg',
                $userParams = ['w' => 100, 'h' => 100]
            );
        }

        // Since we can't really mock the server, we'll generate the md5 hash the same
        // way it does. It will not include the fit parameter since it's not an asset.
        $md5 = $this->getGlideMd5('foo/hoff.jpg', $userParams);

        // While writing this test I noticed that we don't include the domain in the
        // cache path, so the same file path on two different domains will conflict.
        // TODO: Fix this.
        $expectedPath = "http/foo/hoff.jpg/{$md5}.jpg";

        $this->assertEquals($expectedPath, $path);
        $this->assertCount(1, $paths = $this->generatedImagePaths());
        $this->assertContains($expectedPath, $paths);
        $this->assertEquals($expectedPath, Glide::cacheStore()->get($cacheKey));
        Event::assertDispatchedTimes(GlideImageGenerated::class, 1);
    }

    /** @test */
    public function it_validates_an_asset()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    public function it_validates_an_image()
    {
        $this->markTestIncomplete();
    }

    private function makeGenerator()
    {
        return new ImageGenerator($this->app->make(Server::class));
    }

    private function clearGlideCache()
    {
        Glide::cacheStore()->flush();
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
