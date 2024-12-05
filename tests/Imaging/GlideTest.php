<?php

namespace Tests\Imaging;

use Illuminate\Cache\FileStore;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Glide\Server;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Imaging\UrlBuilder;
use Statamic\Facades\Asset;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\File;
use Statamic\Facades\Glide;
use Statamic\Facades\Path;
use Statamic\Imaging\GlideUrlBuilder;
use Statamic\Imaging\ImageGenerator;
use Statamic\Imaging\StaticUrlBuilder;
use Statamic\Support\Str;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class GlideTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function tearDown(): void
    {
        $this->clearGlideCache();

        parent::tearDown();
    }

    #[Test]
    public function cache_false_will_make_a_filesystem_in_the_storage_directory()
    {
        config([
            'statamic.assets.image_manipulation.route' => 'imgs',
            'statamic.assets.image_manipulation.cache' => false,
            'statamic.assets.image_manipulation.cache_path' => public_path('img'), // irrelevant
        ]);

        $cache = Glide::server()->getCache();

        $this->assertLocalAdapter($adapter = $this->getAdapterFromFilesystem($cache));
        $this->assertEquals('public', $this->defaultFolderVisibility($cache));
        $this->assertEquals(storage_path('statamic/glide').DIRECTORY_SEPARATOR, $this->getRootFromLocalAdapter($adapter));
        $this->assertInstanceOf(GlideUrlBuilder::class, $this->app[UrlBuilder::class]);
        $this->assertEquals('/imgs', Glide::url());
    }

    #[Test]
    public function cache_true_will_make_a_filesystem_using_the_cache_path_location()
    {
        config([
            'statamic.assets.image_manipulation.route' => 'imgs',
            'statamic.assets.image_manipulation.cache' => true,
            'statamic.assets.image_manipulation.cache_path' => public_path('imgcache'),
        ]);

        $cache = Glide::server()->getCache();

        $this->assertLocalAdapter($adapter = $this->getAdapterFromFilesystem($cache));
        $this->assertEquals('public', $this->defaultFolderVisibility($cache));
        $this->assertEquals(public_path('imgcache').DIRECTORY_SEPARATOR, $this->getRootFromLocalAdapter($adapter));
        $this->assertInstanceOf(StaticUrlBuilder::class, $this->app[UrlBuilder::class]);
        $this->assertEquals('/imgs', Glide::url());
    }

    #[Test]
    public function cache_true_without_cache_path_will_throw_exception()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Image manipulation cache path is not defined.');

        config([
            'statamic.assets.image_manipulation.route' => 'imgs',
            'statamic.assets.image_manipulation.cache' => true,
            'statamic.assets.image_manipulation.cache_path' => null,
        ]);

        Glide::server()->getCache();
    }

    #[Test]
    public function cache_string_will_use_a_corresponding_filesystem()
    {
        config([
            'statamic.assets.image_manipulation.route' => 'imgs',
            'statamic.assets.image_manipulation.cache' => 'glidecache',
            'filesystems.disks.glidecache' => [
                'driver' => 'local',
                'root' => public_path('diskimgroot'),
                'url' => 'http://the-glide-url',
            ],
        ]);

        $cache = Glide::server()->getCache();

        $this->assertLocalAdapter($adapter = $this->getAdapterFromFilesystem($cache));
        $this->assertEquals(public_path('diskimgroot').DIRECTORY_SEPARATOR, $this->getRootFromLocalAdapter($adapter));
        $this->assertInstanceOf(StaticUrlBuilder::class, $this->app[UrlBuilder::class]);
        $this->assertEquals('http://the-glide-url', Glide::url());
    }

    #[Test]
    public function invalid_cache_string_will_throw_exception()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Disk [glidecache] does not have a configured driver.');

        config(['statamic.assets.image_manipulation.cache' => 'glidecache']);

        Glide::server()->getCache();
    }

    #[Test]
    public function it_gets_the_glide_cache_store()
    {
        config(['cache.stores.glide' => [
            'driver' => 'file',
            'path' => $path = storage_path('custom-glide-store-location'),
        ]]);

        $cache = Glide::cacheStore();

        $this->assertInstanceOf(Repository::class, $cache);
        $this->assertInstanceOf(FileStore::class, $cache->getStore());
        $this->assertEquals($path, $cache->getStore()->getDirectory());
    }

    #[Test]
    public function it_creates_a_glide_cache_store_on_the_fly_if_undefined()
    {
        $cache = Glide::cacheStore();

        $this->assertInstanceOf(Repository::class, $cache);
        $this->assertInstanceOf(FileStore::class, $cache->getStore());
        $this->assertEquals(storage_path('framework/cache/glide'), $cache->getStore()->getDirectory());
    }

    #[Test]
    public function it_deletes_glide_cache_for_an_asset()
    {
        // Should return manifest cache key for an asset, along with 3 manipulation cache keys.
        $this->assertCount(4, $cacheKeys = $this->createImageManipulations('test_container', 'foo/hoff.jpg', 3));

        $this->assertFileExists($glidePath = $this->glideCachePath('containers/test_container/foo/hoff.jpg'));

        $cacheKeys->each(function ($cacheKey) {
            $this->assertTrue(Glide::cacheStore()->has($cacheKey));
        });

        Glide::clearAsset(Asset::find('test_container::foo/hoff.jpg'));

        $this->assertFileDoesNotExist($glidePath);

        $cacheKeys->each(function ($cacheKey) {
            $this->assertFalse(Glide::cacheStore()->has($cacheKey));
        });
    }

    private function assertLocalAdapter($adapter)
    {
        $this->assertInstanceOf(LocalFilesystemAdapter::class, $adapter);
    }

    private function defaultFolderVisibility($filesystem)
    {
        $adapter = $this->getAdapterFromFilesystem($filesystem);

        $reflection = new \ReflectionClass($adapter);
        $visibilityConverter = $reflection->getProperty('visibility');
        $visibilityConverter->setAccessible(true);
        $visibilityConverter = $visibilityConverter->getValue($adapter);

        $reflection = new \ReflectionClass($visibilityConverter);
        $visibility = $reflection->getProperty('defaultForDirectories');
        $visibility->setAccessible(true);

        return $visibility->getValue($visibilityConverter);
    }

    private function getAdapterFromFilesystem($filesystem)
    {
        $reflection = new \ReflectionClass($filesystem);
        $property = $reflection->getProperty('adapter');
        $property->setAccessible(true);

        return $property->getValue($filesystem);
    }

    private function getRootFromLocalAdapter($adapter)
    {
        $reflection = new \ReflectionClass($adapter);
        $property = $reflection->getProperty('prefixer');
        $property->setAccessible(true);
        $prefixer = $property->getValue($adapter);

        return $prefixer->prefixPath('');
    }

    private function clearGlideCache()
    {
        Glide::cacheStore()->flush();
        File::delete(storage_path('statamic/glide'));
    }

    private function glideCachePath($append = null)
    {
        return Path::tidy(collect([storage_path('statamic/glide'), $append])->filter()->implode('/'));
    }

    private function generatedImagePaths()
    {
        return File::getFilesRecursively($this->glideCachePath())
            ->map(fn ($path) => (string) Str::of($path)->after($this->glideCachePath().'/'))
            ->all();
    }

    private function createImageManipulations($containerHandle, $assetPath, $manipulationCount = 1)
    {
        $manifestCacheKey = "asset::{$containerHandle}::{$assetPath}";
        $this->assertNull(Glide::cacheStore()->get($manifestCacheKey));

        Storage::fake('test');
        $filename = pathinfo($assetPath)['basename'];
        $folder = pathinfo($assetPath)['dirname'];
        $file = UploadedFile::fake()->image($assetPath, 30, 60);

        Storage::disk('test')->putFileAs($folder, $file, $filename);
        $container = tap(AssetContainer::make($containerHandle)->disk('test'))->save();
        $asset = tap($container->makeAsset($assetPath))->save();
        $generator = (new ImageGenerator($this->app->make(Server::class)));

        foreach (range(1, $manipulationCount) as $i) {
            $generator->generateByAsset($asset, ['w' => 100, 'h' => $i]);
        }

        $this->assertCount($manipulationCount, $manifest = Glide::cacheStore()->get($manifestCacheKey));
        $this->assertCount($manipulationCount, $this->generatedImagePaths());

        foreach ($manifest as $cacheKey) {
            $this->assertTrue(Str::startsWith($cacheKey, "asset::{$containerHandle}::{$assetPath}::"));
        }

        return collect(array_merge([$manifestCacheKey], $manifest));
    }
}
