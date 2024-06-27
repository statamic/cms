<?php

namespace Tests\Imaging;

use Facades\Statamic\Imaging\ImageValidator;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Glide\Manipulators\Watermark;
use League\Glide\Server;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Events\GlideImageGenerated;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\File;
use Statamic\Facades\Glide;
use Statamic\Imaging\GuzzleAdapter;
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

    #[Test]
    public function it_generates_an_image_by_asset()
    {
        Event::fake();

        $manifestCacheKey = 'asset::test_container::foo/hoff.jpg';
        $manipulationCacheKey = 'asset::test_container::foo/hoff.jpg::4dbc41d8e3ba1ccd302641e509b48768';
        $this->assertNull(Glide::cacheStore()->get($manifestCacheKey));
        $this->assertNull(Glide::cacheStore()->get($manipulationCacheKey));

        Storage::fake('test');
        $file = UploadedFile::fake()->image('foo/hoff.jpg', 30, 60);
        Storage::disk('test')->putFileAs('foo', $file, 'hoff.jpg');
        $container = tap(AssetContainer::make('test_container')->disk('test'))->save();
        $asset = tap($container->makeAsset('foo/hoff.jpg'))->save();

        $this->assertCount(0, $this->generatedImagePaths());

        ImageValidator::shouldReceive('isValidImage')
            ->andReturnTrue()
            ->once(); // Only one manipulation should happen because of cache.

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

        $expectedCacheManifest = [$manipulationCacheKey];
        $expectedPathPrefix = 'containers/test_container';
        $expectedPath = "{$expectedPathPrefix}/foo/hoff.jpg/{$md5}/hoff.jpg";

        $this->assertEquals($manifestCacheKey, ImageGenerator::assetCacheManifestKey($asset));
        $this->assertEquals($expectedPathPrefix, ImageGenerator::assetCachePathPrefix($asset));
        $this->assertEquals($expectedPath, $path);
        $this->assertCount(1, $paths = $this->generatedImagePaths());
        $this->assertContains($expectedPath, $paths);
        $this->assertEquals($expectedCacheManifest, Glide::cacheStore()->get($manifestCacheKey));
        $this->assertEquals($expectedPath, Glide::cacheStore()->get($manipulationCacheKey));
        Event::assertDispatchedTimes(GlideImageGenerated::class, 1);
    }

    #[Test]
    public function it_generates_cache_manifest_for_multiple_asset_manipulations()
    {
        Event::fake();

        $manifestCacheKey = 'asset::test_container::foo/hoff.jpg';
        $this->assertNull(Glide::cacheStore()->get($manifestCacheKey));

        Storage::fake('test');
        $file = UploadedFile::fake()->image('foo/hoff.jpg', 30, 60);
        Storage::disk('test')->putFileAs('foo', $file, 'hoff.jpg');
        $container = tap(AssetContainer::make('test_container')->disk('test'))->save();
        $asset = tap($container->makeAsset('foo/hoff.jpg'))->save();

        ImageValidator::shouldReceive('isValidImage')
            ->andReturnTrue()
            ->times(2); // Two manipulations should get cached.

        // Generate the image twice to make sure it's cached.
        foreach (range(1, 2) as $i) {
            $this->makeGenerator()->generateByAsset(
                $asset,
                ['w' => 100, 'h' => $i] // Ensure unique params so that two manipulations get cached.
            );
        }

        Event::assertDispatchedTimes(GlideImageGenerated::class, 2);

        $this->assertCount(2, $manifest = Glide::cacheStore()->get($manifestCacheKey));
        $this->assertCount(2, $this->generatedImagePaths());

        foreach ($manifest as $cacheKey) {
            $this->assertTrue(Str::startsWith($cacheKey, 'asset::test_container::foo/hoff.jpg::'));
        }
    }

    #[Test]
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

        ImageValidator::shouldReceive('isValidImage')
            ->andReturnTrue()
            ->once(); // Only one manipulation should happen because of cache.

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

        $expectedPath = "paths/testimages/foo/hoff.jpg/{$md5}/hoff.jpg";

        $this->assertEquals($expectedPath, $path);
        $this->assertCount(1, $paths = $this->generatedImagePaths());
        $this->assertContains($expectedPath, $paths);
        $this->assertEquals($expectedPath, Glide::cacheStore()->get($cacheKey));
        Event::assertDispatchedTimes(GlideImageGenerated::class, 1);
    }

    #[Test]
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
        $expectedPath = "http/foo/hoff.jpg/{$md5}/hoff.jpg";

        $this->assertEquals($expectedPath, $path);
        $this->assertCount(1, $paths = $this->generatedImagePaths());
        $this->assertContains($expectedPath, $paths);
        $this->assertEquals($expectedPath, Glide::cacheStore()->get($cacheKey));
        Event::assertDispatchedTimes(GlideImageGenerated::class, 1);
    }

    #[Test]
    public function the_watermark_disk_is_the_public_directory_by_default()
    {
        $generator = $this->makeGenerator();

        $filesystem = $this->getWatermarkFilesystem($generator);

        $this->assertLocalAdapter($adapter = $this->getAdapterFromFilesystem($filesystem));
        $this->assertEquals(public_path().DIRECTORY_SEPARATOR, $this->getRootFromLocalAdapter($adapter));
    }

    #[Test]
    public function the_watermark_disk_is_the_container_when_an_asset_is_provided()
    {
        // Make the asset to be used as the watermark.
        Storage::fake('test');
        $file = UploadedFile::fake()->image('foo/hoff.jpg', 30, 60);
        Storage::disk('test')->putFileAs('foo', $file, 'hoff.jpg');
        $container = tap(AssetContainer::make('test_container')->disk('test'))->save();
        $asset = tap($container->makeAsset('foo/hoff.jpg'))->save();

        $generator = $this->makeGenerator();

        $generator->setParams(['mark' => $asset]);

        $filesystem = $this->getWatermarkFilesystem($generator);

        $this->assertSame($container->disk()->filesystem()->getDriver(), $filesystem);
        $this->assertEquals(['mark' => 'foo/hoff.jpg'], $generator->getParams());
    }

    #[Test]
    public function the_watermark_disk_is_the_container_when_an_asset_encoded_url_string_is_provided()
    {
        // Make the asset to be used as the watermark.
        Storage::fake('test');
        $file = UploadedFile::fake()->image('foo/hoff.jpg', 30, 60);
        Storage::disk('test')->putFileAs('foo', $file, 'hoff.jpg');
        $container = tap(AssetContainer::make('test_container')->disk('test'))->save();
        $asset = tap($container->makeAsset('foo/hoff.jpg'))->save();

        $generator = $this->makeGenerator();

        $generator->setParams(['mark' => 'asset::'.base64_encode('test_container/foo/hoff.jpg')]);

        $filesystem = $this->getWatermarkFilesystem($generator);

        $this->assertSame($container->disk()->filesystem()->getDriver(), $filesystem);
        $this->assertEquals(['mark' => 'foo/hoff.jpg'], $generator->getParams());
    }

    #[Test]
    public function the_watermark_disk_is_a_local_adapter_when_a_path_is_provided()
    {
        $generator = $this->makeGenerator();

        $generator->setParams(['mark' => 'foo/hoff.jpg']);

        $filesystem = $this->getWatermarkFilesystem($generator);

        $this->assertLocalAdapter($adapter = $this->getAdapterFromFilesystem($filesystem));
        $this->assertEquals(public_path().DIRECTORY_SEPARATOR, $this->getRootFromLocalAdapter($adapter));
        $this->assertEquals(['mark' => 'foo/hoff.jpg'], $generator->getParams());
    }

    #[Test]
    #[DataProvider('guzzleWatermarkProvider')]
    public function the_watermark_disk_is_a_guzzle_adapter_when_a_url_is_provided($protocol)
    {
        $generator = $this->makeGenerator();

        $generator->setParams(['mark' => $protocol.'://example.com/foo/hoff.jpg']);

        $filesystem = $this->getWatermarkFilesystem($generator);

        $this->assertGuzzleAdapter($this->getAdapterFromFilesystem($filesystem));
        $this->assertEquals(['mark' => 'foo/hoff.jpg'], $generator->getParams());
    }

    public static function guzzleWatermarkProvider()
    {
        return ['http' => ['http'], 'https' => ['https']];
    }

    private function getWatermarkFilesystem(ImageGenerator $generator)
    {
        $manipulators = $generator->getServer()->getApi()->getManipulators();

        $watermark = collect($manipulators)->first(fn ($m) => $m instanceof Watermark);

        return $watermark->getWatermarks();
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

    private function assertLocalAdapter($adapter)
    {
        $this->assertInstanceOf(LocalFilesystemAdapter::class, $adapter);
    }

    private function assertGuzzleAdapter($adapter)
    {
        $this->assertInstanceOf(GuzzleAdapter::class, $adapter);
    }

    private function getRootFromLocalAdapter($adapter)
    {
        $reflection = new \ReflectionClass($adapter);
        $property = $reflection->getProperty('prefixer');
        $property->setAccessible(true);
        $prefixer = $property->getValue($adapter);

        return $prefixer->prefixPath('');
    }

    private function getAdapterFromFilesystem($filesystem)
    {
        $reflection = new \ReflectionClass($filesystem);
        $property = $reflection->getProperty('adapter');
        $property->setAccessible(true);

        return $property->getValue($filesystem);
    }
}
