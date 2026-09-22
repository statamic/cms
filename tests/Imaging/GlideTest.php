<?php

namespace Tests\Imaging;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Illuminate\Cache\FileStore;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Glide\Server;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Console\Processes\Ffmpeg;
use Statamic\Contracts\Assets\Asset as AssetContract;
use Statamic\Contracts\Imaging\UrlBuilder;
use Statamic\Facades\Asset;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Config;
use Statamic\Facades\File;
use Statamic\Facades\Glide;
use Statamic\Facades\Path;
use Statamic\Facades\Site;
use Statamic\Imaging\GlideCachePathResolver;
use Statamic\Imaging\GlideUrlBuilder;
use Statamic\Imaging\HybridUrlBuilder;
use Statamic\Imaging\ImageGenerator;
use Statamic\Imaging\RemoteUrlValidator;
use Statamic\Imaging\StaticUrlBuilder;
use Statamic\Providers\GlideServiceProvider;
use Statamic\Support\Str;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class GlideTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function tearDown(): void
    {
        $this->clearGlideCache();

        File::delete(public_path('img'));
        File::delete(public_path('test-path.jpg'));
        File::delete(public_path('mark.png'));

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
    public function hybrid_caching_will_make_a_filesystem_using_the_cache_path_location()
    {
        config([
            'statamic.assets.image_manipulation.route' => 'imgs',
            'statamic.assets.image_manipulation.cache' => 'hybrid',
            'statamic.assets.image_manipulation.cache_path' => public_path('imgcache'),
        ]);

        $cache = Glide::server()->getCache();

        $this->assertLocalAdapter($adapter = $this->getAdapterFromFilesystem($cache));
        $this->assertEquals('public', $this->defaultFolderVisibility($cache));
        $this->assertEquals(public_path('imgcache').DIRECTORY_SEPARATOR, $this->getRootFromLocalAdapter($adapter));
        $this->assertInstanceOf(HybridUrlBuilder::class, $this->app[UrlBuilder::class]);
        $this->assertEquals('/imgs', Glide::url());
    }

    #[Test]
    public function hybrid_caching_without_cache_path_will_throw_exception()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Image manipulation cache path is not defined.');

        config([
            'statamic.assets.image_manipulation.route' => 'imgs',
            'statamic.assets.image_manipulation.cache' => 'hybrid',
            'statamic.assets.image_manipulation.cache_path' => null,
        ]);

        Glide::server()->getCache();
    }

    #[Test]
    public function hybrid_caching_is_detected_as_half_measure()
    {
        config(['statamic.assets.image_manipulation.cache' => 'hybrid']);

        $this->assertTrue(Glide::isUsingHybridCaching());
        $this->assertFalse(Glide::shouldServeDirectly());
        $this->assertFalse(Glide::shouldServeByHttp());
    }

    #[Test]
    #[DataProvider('cachePathServedByRouteProvider')]
    public function hybrid_caching_knows_when_the_cache_path_is_served_by_the_route($route, $cachePath, $expected)
    {
        config([
            'statamic.assets.image_manipulation.cache' => 'hybrid',
            'statamic.assets.image_manipulation.route' => $route,
            'statamic.assets.image_manipulation.cache_path' => $cachePath(),
        ]);

        $this->assertSame($expected, Glide::cachePathIsServedByRoute());
    }

    public static function cachePathServedByRouteProvider()
    {
        return [
            'matching' => ['img', fn () => public_path('img'), true],
            'matching with slashes' => ['/img/', fn () => public_path('img/'), true],
            'matching absolute route' => ['http://localhost/img', fn () => public_path('img'), true],
            'matching nested' => ['assets/img', fn () => public_path('assets/img'), true],
            'different directory' => ['img', fn () => public_path('imgcache'), false],
            'outside public' => ['img', fn () => storage_path('img'), false],
        ];
    }

    #[Test]
    public function hybrid_caching_warns_once_when_the_cache_path_is_not_served_by_the_route()
    {
        config([
            'statamic.assets.image_manipulation.cache' => 'hybrid',
            'statamic.assets.image_manipulation.route' => 'img',
            'statamic.assets.image_manipulation.cache_path' => public_path('imgcache'),
        ]);

        Log::shouldReceive('warning')->once()->withArgs(fn ($message) => str_contains($message, 'hybrid'));

        (new GlideServiceProvider($this->app))->boot();
        (new GlideServiceProvider($this->app))->boot();
    }

    #[Test]
    public function hybrid_caching_does_not_warn_when_the_cache_path_is_served_by_the_route()
    {
        config([
            'statamic.assets.image_manipulation.cache' => 'hybrid',
            'statamic.assets.image_manipulation.route' => 'img',
            'statamic.assets.image_manipulation.cache_path' => public_path('img'),
        ]);

        Log::shouldReceive('warning')->never();

        (new GlideServiceProvider($this->app))->boot();
    }

    #[Test]
    #[DefineEnvironment('hybridCaching')]
    #[DataProvider('hybridItemProvider')]
    public function hybrid_caching_generates_the_image_where_the_url_points($setUp)
    {
        [$item, $params] = $setUp($this);

        $url = $this->app->make(UrlBuilder::class)->build($item, $params);
        $servedPath = public_path(rawurldecode($url));

        $this->assertStringStartsWith('/img/', $url);
        $this->assertFileDoesNotExist($servedPath);

        $response = $this->get($url);

        $response->assertOk();
        $response->streamedContent();
        $this->assertFileExists($servedPath);
    }

    public static function hybridItemProvider()
    {
        return [
            'asset' => [fn ($test) => [$test->createAsset(), ['w' => 100]]],
            'asset with auto crop' => [function ($test) {
                config(['statamic.assets.auto_crop' => true]);

                return [$test->createAsset(), ['w' => 100, 'h' => 50]];
            }],
            'asset with reserved characters in its filename' => [fn ($test) => [$test->createAsset('foo/photo #1.jpg'), ['w' => 100]]],
            'asset id' => [fn ($test) => [$test->createAsset()->id(), ['w' => 100]]],
            'asset on a subdirectory site' => [function ($test) {
                $test->setSites([
                    'english' => ['url' => '/', 'locale' => 'en_US'],
                    'french' => ['url' => '/fr/', 'locale' => 'fr_FR'],
                ]);

                Site::setCurrent('french');

                return [$test->createAsset(), ['w' => 100]];
            }],
            'video asset' => [function ($test) {
                $test->fakeFfmpeg();

                return [$test->createAsset('foo/clip.mp4'), ['w' => 100]];
            }],
            'path' => [fn ($test) => [$test->createPublicImage('test-path.jpg'), ['w' => 100]]],
            'remote url' => [function ($test) {
                $test->bindRemoteImage();

                return ['https://example.com/foo/hoff.jpg', ['w' => 100]];
            }],
            'remote url with a query string' => [function ($test) {
                $test->bindRemoteImage();

                return ['https://example.com/foo/hoff.jpg?query=david', ['w' => 100]];
            }],
            'remote url with an encoded character' => [function ($test) {
                $test->bindRemoteImage();

                return ['https://example.com/foo/photo%23one.jpg', ['w' => 100]];
            }],
            'asset watermark' => [fn ($test) => [$test->createAsset(), ['w' => 100, 'mark' => $test->createAsset('foo/mark.png')]]],
            'path watermark' => [fn ($test) => [$test->createAsset(), ['w' => 100, 'mark' => $test->createPublicImage('mark.png')]]],
            'remote url watermark' => [function ($test) {
                $test->bindRemoteImage();

                return [$test->createAsset(), ['w' => 100, 'mark' => 'https://example.com/mark.png']];
            }],
        ];
    }

    #[Test]
    #[DefineEnvironment('hybridCaching')]
    public function hybrid_caching_serves_existing_cached_file()
    {
        $fakePath = 'containers/test/fake-hash/image.jpg';
        $image = UploadedFile::fake()->image('image.jpg', 10, 10);
        Glide::cacheDisk()->put($fakePath, file_get_contents($image->getPathname()));

        $response = $this->get('/img/'.$fakePath);

        $response->assertOk();
    }

    #[Test]
    #[DefineEnvironment('hybridCaching')]
    public function hybrid_caching_warns_once_when_an_existing_image_is_served_through_php()
    {
        $fakePath = 'containers/test/fake-hash/image.jpg';
        $image = UploadedFile::fake()->image('image.jpg', 10, 10);
        Glide::cacheDisk()->put($fakePath, file_get_contents($image->getPathname()));

        Log::shouldReceive('warning')->once()->withArgs(fn ($message) => str_contains($message, 'served by PHP'));

        $this->get('/img/'.$fakePath)->assertOk();
        $this->get('/img/'.$fakePath)->assertOk();
    }

    #[Test]
    #[DefineEnvironment('hybridCaching')]
    public function hybrid_caching_does_not_warn_when_the_image_is_generated_on_demand()
    {
        $asset = $this->createAsset();
        $url = $this->app->make(UrlBuilder::class)->build($asset, ['w' => 100]);

        Log::shouldReceive('warning')->never();

        $this->get($url)->assertOk()->streamedContent();
    }

    #[Test]
    #[DefineEnvironment('hybridCaching')]
    public function hybrid_caching_returns_404_when_no_mapping_exists()
    {
        $response = $this->get('/img/containers/nonexistent/hash/image.jpg');

        $response->assertNotFound();
    }

    #[Test]
    #[DefineEnvironment('hybridCaching')]
    public function hybrid_caching_returns_404_when_path_traverses_outside_the_cache()
    {
        $response = $this->get('/img/../../.env');

        $response->assertNotFound();
    }

    #[Test]
    #[DefineEnvironment('hybridCaching')]
    public function hybrid_caching_regenerates_when_file_deleted_but_mapping_exists()
    {
        $asset = $this->createAsset();
        $url = $this->app->make(UrlBuilder::class)->build($asset, ['w' => 100]);

        $resolver = new GlideCachePathResolver($this->app->make(Server::class));
        $expectedPath = $resolver->resolveForAsset($asset, ['w' => 100]);

        // Generate the image
        $response = $this->get($url);
        $response->assertOk();
        $response->streamedContent(); // Ensure the file handle is closed (Windows compat)
        $this->assertTrue(Glide::cacheDisk()->exists($expectedPath));

        // Delete the file but leave the mapping
        Glide::cacheDisk()->delete($expectedPath);
        $this->assertFalse(Glide::cacheDisk()->exists($expectedPath));

        // Request again — should regenerate via the mapping
        $response = $this->get($url);
        $response->assertOk();
        $this->assertTrue(Glide::cacheDisk()->exists($expectedPath));
    }

    #[Test]
    #[DefineEnvironment('hybridCaching')]
    public function hybrid_caching_url_has_no_query_params()
    {
        $asset = $this->createAsset();
        $url = $this->app->make(UrlBuilder::class)->build($asset, ['w' => 100]);

        $this->assertStringNotContainsString('?', $url);
        $this->assertStringStartsWith('/img/', $url);
    }

    #[Test]
    #[DefineEnvironment('hybridCaching')]
    public function hybrid_caching_generates_image_on_first_request_by_url()
    {
        $this->app->bind(RemoteUrlValidator::class, fn () => new RemoteUrlValidator(function () {
            throw new \Exception('The host should not be resolved when building a URL.');
        }));

        $url = $this->app->make(UrlBuilder::class)->build('https://example.com/foo/hoff.jpg', ['w' => 100]);
        $expectedPath = Str::after($url, '/img/');

        $this->assertStringStartsWith('/img/http/foo/hoff.jpg/', $url);
        $this->assertSame([
            'type' => 'url',
            'url' => 'https://example.com/foo/hoff.jpg',
            'params' => ['w' => 100],
        ], Glide::cacheStore()->get('hybrid::'.$expectedPath));
        $this->assertFalse(Glide::cacheDisk()->exists($expectedPath));

        $this->bindRemoteImage();

        $response = $this->get($url);

        $response->assertOk();
        $response->assertHeader('content-type', 'image/jpeg');
        $this->assertTrue(Glide::cacheDisk()->exists($expectedPath));
    }

    #[Test]
    #[DefineEnvironment('hybridCaching')]
    public function hybrid_caching_only_registers_the_cache_path_route()
    {
        $asset = $this->createAsset();

        $uris = collect(Route::getRoutes()->getRoutes())->map->uri();

        $this->assertContains('img/{path}', $uris->all());
        $this->assertNotContains('img/asset/{container}/{path?}', $uris->all());
        $this->assertNotContains('img/http/{url}/{filename?}', $uris->all());

        $signedUrl = (new GlideUrlBuilder(['key' => Config::getAppKey(), 'route' => '/img']))->build($asset, ['w' => 100]);

        $this->get($signedUrl)->assertNotFound();
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
        $visibilityConverter = $visibilityConverter->getValue($adapter);

        $reflection = new \ReflectionClass($visibilityConverter);
        $visibility = $reflection->getProperty('defaultForDirectories');

        return $visibility->getValue($visibilityConverter);
    }

    private function getAdapterFromFilesystem($filesystem)
    {
        $reflection = new \ReflectionClass($filesystem);
        $property = $reflection->getProperty('adapter');

        return $property->getValue($filesystem);
    }

    private function getRootFromLocalAdapter($adapter)
    {
        $reflection = new \ReflectionClass($adapter);
        $property = $reflection->getProperty('prefixer');
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

    private function createAsset(string $path = 'foo/hoff.jpg'): AssetContract
    {
        if (! $container = AssetContainer::find('test_container')) {
            Storage::fake('test');
            $container = tap(AssetContainer::make('test_container')->disk('test'))->save();
        }

        $file = UploadedFile::fake()->image(basename($path), 30, 60);
        Storage::disk('test')->putFileAs(dirname($path), $file, basename($path));

        return tap($container->makeAsset($path))->save();
    }

    private function createPublicImage(string $path): string
    {
        $file = UploadedFile::fake()->image(basename($path), 30, 60);

        file_put_contents(public_path($path), file_get_contents($file->getPathname()));

        return $path;
    }

    private function bindRemoteImage()
    {
        $this->app->bind(RemoteUrlValidator::class, fn () => new RemoteUrlValidator(fn () => [['ip' => '93.184.216.34']]));

        $this->app->bind('statamic.imaging.guzzle', function () {
            $file = UploadedFile::fake()->image('', 30, 60);
            $response = new Response(200, [], file_get_contents($file->getPathname()));

            return new Client(['handler' => new MockHandler([$response, $response, $response])]);
        });
    }

    private function fakeFfmpeg()
    {
        $this->mock(Ffmpeg::class, function ($mock) {
            $mock->shouldReceive('available')->andReturnTrue();
            $mock->shouldReceive('extractThumbnail')->andReturnUsing(function ($input, $output) {
                $thumbnail = UploadedFile::fake()->image('thumbnail.jpg', 30, 60);

                return tap($output, fn () => file_put_contents($output, file_get_contents($thumbnail->getPathname())));
            });
        });
    }

    protected function hybridCaching($app)
    {
        $app['config']->set('statamic.assets.image_manipulation.cache', 'hybrid');
        $app['config']->set('statamic.assets.image_manipulation.cache_path', public_path('img'));
        $app['config']->set('statamic.assets.image_manipulation.route', 'img');
    }
}
