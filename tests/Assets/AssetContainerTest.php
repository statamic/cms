<?php

namespace Tests\Assets;

use Facades\Statamic\Fields\BlueprintRepository;
use Illuminate\Cache\Events\CacheHit;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Statamic\Assets\Asset;
use Statamic\Assets\AssetContainer;
use Statamic\Contracts\Assets\Asset as AssetContract;
use Statamic\Contracts\Assets\AssetFolder;
use Statamic\Facades;
use Statamic\Facades\Blink;
use Statamic\Facades\File;
use Statamic\Fields\Blueprint;
use Statamic\Filesystem\Filesystem;
use Statamic\Filesystem\FlysystemAdapter;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class AssetContainerTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function it_gets_and_sets_the_id()
    {
        $container = new AssetContainer;
        $this->assertNull($container->id());

        $return = $container->id('123');

        $this->assertEquals($container, $return);
        $this->assertEquals('123', $container->id());
    }

    /** @test */
    public function it_gets_and_sets_the_handle()
    {
        $container = new AssetContainer;
        $this->assertNull($container->handle());

        $return = $container->handle('123');

        $this->assertEquals($container, $return);
        $this->assertEquals('123', $container->handle());
    }

    /** @test */
    public function it_changes_the_handle_when_changing_the_id()
    {
        // only applies to a file implementation

        $container = (new AssetContainer)->handle('handle');
        $container->id('id');
        $this->assertEquals('id', $container->handle());
    }

    /** @test */
    public function it_changes_the_id_when_changing_the_handle()
    {
        // only applies to a file implementation

        $container = (new AssetContainer)->id('id');
        $container->handle('handle');
        $this->assertEquals('handle', $container->id());
    }

    /** @test */
    public function it_gets_and_sets_the_disk()
    {
        config(['filesystems.disks.test' => [
            'driver' => 'local',
            'root' => __DIR__.'/__fixtures__/container',
            'url' => '/the-url',
        ]]);

        $container = new AssetContainer;
        $this->assertNull($container->disk());

        $return = $container->disk('test');

        $this->assertEquals($container, $return);
        $this->assertInstanceOf(FlysystemAdapter::class, $container->disk());
        $this->assertEquals('test', $container->diskHandle());
        $this->assertEquals('/the-url', $container->disk()->filesystem()->getDriver()->getConfig()->get('url'));
    }

    /** @test */
    public function it_gets_the_url_from_the_disk_config()
    {
        config(['filesystems.disks.test' => [
            'driver' => 'local',
            'root' => __DIR__.'/__fixtures__/container',
            'url' => 'http://example.com/container',
        ]]);

        $container = (new AssetContainer)->disk('test');

        $this->assertEquals('http://example.com/container', $container->url());
        $this->assertEquals('http://example.com/container', $container->absoluteUrl());
    }

    /** @test */
    public function it_gets_the_url_from_the_disk_config_when_its_relative()
    {
        config(['filesystems.disks.test' => [
            'driver' => 'local',
            'root' => __DIR__.'/__fixtures__/container',
            'url' => '/container',
        ]]);

        $container = (new AssetContainer)->disk('test');

        $this->assertEquals('/container', $container->url());
        $this->assertEquals('http://localhost/container', $container->absoluteUrl());
    }

    /** @test */
    public function its_private_if_the_disk_has_no_url()
    {
        Storage::fake('test');

        $container = (new AssetContainer)->disk('test');
        $this->assertTrue($container->private());
        $this->assertFalse($container->accessible());

        Storage::disk('test')->getDriver()->getConfig()->set('url', '/url');

        $this->assertFalse($container->private());
        $this->assertTrue($container->accessible());
    }

    /** @test */
    public function it_gets_and_sets_the_title()
    {
        $container = (new AssetContainer)->handle('main');
        $this->assertEquals('Main', $container->title());

        $return = $container->title('Main Assets');

        $this->assertEquals($container, $return);
        $this->assertEquals('Main Assets', $container->title());
    }

    /** @test */
    public function it_gets_the_blueprint()
    {
        BlueprintRepository::shouldReceive('find')->with('assets/main')->andReturn($blueprint = new Blueprint);

        $container = (new AssetContainer)->handle('main');
        $this->assertEquals($blueprint, $container->blueprint());
    }

    /** @test */
    public function it_gets_and_sets_whether_uploads_are_allowed()
    {
        $container = new AssetContainer;
        $this->assertTrue($container->allowUploads());

        $return = $container->allowUploads(false);

        $this->assertEquals($container, $return);
        $this->assertFalse($container->allowUploads());
    }

    /** @test */
    public function it_gets_and_sets_whether_folders_can_be_created()
    {
        $container = new AssetContainer;
        $this->assertTrue($container->createFolders());

        $return = $container->createFolders(false);

        $this->assertEquals($container, $return);
        $this->assertFalse($container->createFolders());
    }

    /** @test */
    public function it_saves_the_container_through_the_api()
    {
        Facades\AssetContainer::spy();

        $container = new AssetContainer;

        $return = $container->save();

        $this->assertEquals($container, $return);
        Facades\AssetContainer::shouldHaveReceived('save')->with($container)->once();
    }

    /** @test */
    public function it_gets_the_path_from_the_stache()
    {
        $container = (new AssetContainer)->handle('test');

        $this->assertEquals($this->fakeStacheDirectory.'/content/assets/test.yaml', $container->path());
    }

    /** @test */
    public function it_gets_all_files_by_default()
    {
        $this->assertEquals([
            'a.txt',
            'b.txt',
            'nested/double-nested/double-nested-a.txt',
            'nested/double-nested/double-nested-b.txt',
            'nested/nested-a.txt',
            'nested/nested-b.txt',
        ], $this->containerWithDisk()->files()->all());
    }

    /** @test */
    public function it_gets_files_in_a_folder()
    {
        $this->assertEquals([
            'a.txt',
            'b.txt',
        ], $this->containerWithDisk()->files('/')->all());

        $this->assertEquals([
            'nested/nested-a.txt',
            'nested/nested-b.txt',
        ], $this->containerWithDisk()->files('nested')->all());
    }

    /** @test */
    public function it_gets_files_in_a_folder_recursively()
    {
        $this->assertEquals([
            'a.txt',
            'b.txt',
            'nested/double-nested/double-nested-a.txt',
            'nested/double-nested/double-nested-b.txt',
            'nested/nested-a.txt',
            'nested/nested-b.txt',
        ], $this->containerWithDisk()->files('/', true)->all());

        $this->assertEquals([
            'nested/double-nested/double-nested-a.txt',
            'nested/double-nested/double-nested-b.txt',
            'nested/nested-a.txt',
            'nested/nested-b.txt',
        ], $this->containerWithDisk()->files('nested', true)->all());
    }

    /** @test */
    public function it_gets_all_folders_by_default()
    {
        $this->assertEquals([
            'nested',
            'nested/double-nested',
        ], $this->containerWithDisk()->folders()->all());

        $this->assertEquals([
            'nested',
            'nested/double-nested',
        ], $this->containerWithDisk()->assetFolders()->map->path()->values()->all());
    }

    /** @test */
    public function it_gets_folders_in_given_folder()
    {
        $this->assertEquals([
            'nested',
        ], $this->containerWithDisk()->folders('/')->all());

        $this->assertEquals([
            'nested',
        ], $this->containerWithDisk()->assetFolders('/')->map->path()->values()->all());

        $this->assertEquals([
            'nested/double-nested',
        ], $this->containerWithDisk()->folders('nested')->all());

        $this->assertEquals([
            'nested/double-nested',
        ], $this->containerWithDisk()->assetFolders('nested')->map->path()->values()->all());
    }

    /** @test */
    public function it_gets_folders_in_given_folder_recursively()
    {
        $this->assertEquals([
            'nested',
            'nested/double-nested',
        ], $this->containerWithDisk()->folders('/', true)->all());

        $this->assertEquals([
            'nested',
            'nested/double-nested',
        ], $this->containerWithDisk()->assetFolders('/', true)->map->path()->values()->all());

        $this->assertEquals([
            'nested/double-nested',
        ], $this->containerWithDisk()->folders('nested', true)->all());

        $this->assertEquals([
            'nested/double-nested',
        ], $this->containerWithDisk()->assetFolders('nested', true)->map->path()->values()->all());
    }

    /** @test */
    public function it_gets_the_files_from_the_filesystem_only_once()
    {
        Carbon::setTestNow(now()->startOfMinute());

        $disk = $this->mock(Filesystem::class);
        $disk->shouldReceive('filesystem->getDriver->listContents')
            ->with('/', true)
            ->once()
            ->andReturn([
                '.meta/one.jpg.yaml' => ['type' => 'file', 'path' => '.meta/one.jpg.yaml', 'basename' => 'one.jpg.yaml'],
                '.DS_Store' => ['type' => 'file', 'path' => '.DS_Store', 'basename' => '.DS_Store'],
                '.gitignore' => ['type' => 'file', 'path' => '.gitignore', 'basename' => '.gitignore'],
                'one.jpg' => ['type' => 'file', 'path' => 'one.jpg', 'basename' => 'one.jpg'],
                'two.jpg' => ['type' => 'file', 'path' => 'two.jpg', 'basename' => 'two.jpg'],
            ]);

        File::shouldReceive('disk')->with('test')->andReturn($disk);

        $this->assertFalse(Cache::has($cacheKey = 'asset-list-contents-test'));
        $this->assertFalse(Blink::has($cacheKey));

        $container = (new AssetContainer)->handle('test')->disk('test');

        $first = $container->files();
        $second = $container->files();

        $expected = ['one.jpg', 'two.jpg'];
        $this->assertEquals($expected, $first->all());
        $this->assertEquals($expected, $second->all());
        $this->assertTrue(Cache::has($cacheKey));

        Carbon::setTestNow(now()->addYears(5)); // i.e. forever.
        $this->assertTrue(Cache::has($cacheKey));
    }

    /** @test */
    public function it_gets_the_files_from_the_cache_only_once()
    {
        $cacheKey = 'asset-list-contents-test';

        Cache::put($cacheKey, collect([
            '.meta/one.jpg.yaml' => ['type' => 'file', 'path' => '.meta/one.jpg.yaml', 'basename' => 'one.jpg.yaml'],
            '.DS_Store' => ['type' => 'file', 'path' => '.DS_Store', 'basename' => '.DS_Store'],
            '.gitignore' => ['type' => 'file', 'path' => '.gitignore', 'basename' => '.gitignore'],
            'one.jpg' => ['type' => 'file', 'path' => 'one.jpg', 'basename' => 'one.jpg'],
            'two.jpg' => ['type' => 'file', 'path' => 'two.jpg', 'basename' => 'two.jpg'],
        ]));

        $cacheHits = 0;
        Event::listen(CacheHit::class, function ($event) use (&$cacheHits, $cacheKey) {
            if ($event->key === $cacheKey) {
                $cacheHits++;
            }
        });

        $container = (new AssetContainer)->handle('test')->disk('test');

        $expected = ['one.jpg', 'two.jpg'];
        $this->assertEquals($expected, $container->files()->all());
        $this->assertEquals(1, $cacheHits);
        $this->assertEquals($expected, $container->files()->all());
        $this->assertEquals(1, $cacheHits);
    }

    /** @test */
    public function it_gets_the_folders_from_the_filesystem_only_once()
    {
        Carbon::setTestNow(now()->startOfMinute());

        $disk = $this->mock(Filesystem::class);
        $disk->shouldReceive('filesystem->getDriver->listContents')
            ->with('/', true)
            ->once()
            ->andReturn([
                '.meta' => ['type' => 'dir', 'path' => '.meta', 'basename' => '.meta'],
                'one' => ['type' => 'dir', 'path' => 'one', 'basename' => 'one'],
                'one/.meta' => ['type' => 'dir', 'path' => 'one/.meta', 'basename' => '.meta'],
                'two' => ['type' => 'dir', 'path' => 'two', 'basename' => 'two'],
            ]);

        File::shouldReceive('disk')->with('test')->andReturn($disk);

        $this->assertFalse(Cache::has($cacheKey = 'asset-list-contents-test'));
        $this->assertFalse(Blink::has($cacheKey));

        $container = (new AssetContainer)->handle('test')->disk('test');

        $first = $container->folders();
        $second = $container->folders();

        $expected = ['one', 'two'];
        $this->assertEquals($expected, $first->all());
        $this->assertEquals($expected, $second->all());
        $this->assertTrue(Cache::has($cacheKey));

        Carbon::setTestNow(now()->addYears(5)); // i.e. forever.
        $this->assertTrue(Cache::has($cacheKey));
    }

    /** @test */
    public function it_gets_the_folders_from_the_cache_and_blink_only_once()
    {
        $cacheKey = 'asset-list-contents-test';

        Cache::put($cacheKey, collect([
            '.meta' => ['type' => 'dir', 'path' => '.meta', 'basename' => '.meta'],
            'one' => ['type' => 'dir', 'path' => 'one', 'basename' => 'one'],
            'one/.meta' => ['type' => 'dir', 'path' => 'one/.meta', 'basename' => '.meta'],
            'two' => ['type' => 'dir', 'path' => 'two', 'basename' => 'two'],
        ]));

        $cacheHits = 0;
        Event::listen(CacheHit::class, function ($event) use (&$cacheHits, $cacheKey) {
            if ($event->key === $cacheKey) {
                $cacheHits++;
            }
        });

        $container = (new AssetContainer)->handle('test')->disk('test');

        $expected = ['one', 'two'];
        $this->assertEquals($expected, $container->folders()->all());
        $this->assertEquals(1, $cacheHits);
        $this->assertEquals($expected, $container->folders()->all());
        $this->assertEquals(1, $cacheHits);

        // This checks that we're using blink to persist across instances in case the container
        // was newed up again. If wouldn't persist if we simply put the cache into a property.
        $anotherInstanceOfTheContainer = (new AssetContainer)->handle('test')->disk('test');
        $this->assertEquals($expected, $anotherInstanceOfTheContainer->folders()->all());
        $this->assertEquals(1, $cacheHits);
    }

    /** @test */
    public function it_gets_an_asset()
    {
        $asset = $this->containerWithDisk()->asset('a.txt');

        $this->assertInstanceOf(AssetContract::class, $asset);
    }

    /** @test */
    public function it_gets_an_asset_with_data()
    {
        $container = $this->containerWithDisk();

        tap($container->asset('a.txt'), function ($asset) {
            $this->assertInstanceOf(AssetContract::class, $asset);
            $this->assertEquals('File A', $asset->get('title'));
        });

        $this->assertNull($container->asset('non-existent.txt'));
    }

    /** @test */
    public function it_makes_an_asset_at_given_path()
    {
        $container = new AssetContainer;
        $asset = $container->makeAsset('path/to/test.txt');

        $this->assertInstanceOf(Asset::class, $asset);
        $this->assertEquals($container, $asset->container());
        $this->assertEquals('path/to/test.txt', $asset->path());
    }

    /** @test */
    public function it_gets_all_assets_by_default()
    {
        $assets = $this->containerWithDisk()->assets();

        $this->assertInstanceOf(Collection::class, $assets);
        $this->assertCount(6, $assets);
        $this->assertEveryItemIsInstanceOf(Asset::class, $assets);
        $this->assertEquals([
            'a.txt',
            'b.txt',
            'nested/double-nested/double-nested-a.txt',
            'nested/double-nested/double-nested-b.txt',
            'nested/nested-a.txt',
            'nested/nested-b.txt',
        ], $assets->map->path()->values()->all());
    }

    /** @test */
    public function it_gets_assets_in_a_folder()
    {
        tap($this->containerWithDisk()->assets('/'), function ($assets) {
            $this->assertInstanceOf(Collection::class, $assets);
            $this->assertCount(2, $assets);
            $this->assertEveryItemIsInstanceOf(Asset::class, $assets);
        });

        tap($this->containerWithDisk()->assets('nested'), function ($assets) {
            $this->assertInstanceOf(Collection::class, $assets);
            $this->assertCount(2, $assets);
            $this->assertEveryItemIsInstanceOf(Asset::class, $assets);
        });
    }

    /** @test */
    public function it_gets_assets_in_a_folder_recursively()
    {
        tap($this->containerWithDisk()->assets('/', true), function ($assets) {
            $this->assertInstanceOf(Collection::class, $assets);
            $this->assertCount(6, $assets);
            $this->assertEveryItemIsInstanceOf(Asset::class, $assets);
        });

        tap($this->containerWithDisk()->assets('nested', true), function ($assets) {
            $this->assertInstanceOf(Collection::class, $assets);
            $this->assertCount(4, $assets);
            $this->assertEveryItemIsInstanceOf(Asset::class, $assets);
        });
    }

    /** @test */
    public function it_gets_an_asset_folder()
    {
        Storage::fake('test');
        $container = $this->containerWithDisk();

        $folder = $container->assetFolder('foo');

        $this->assertInstanceOf(AssetFolder::class, $folder);
        $this->assertEquals('foo', $folder->title());
        $this->assertEquals('foo', $folder->path());
        $this->assertEquals($container, $folder->container());
    }

    private function containerWithDisk()
    {
        config(['filesystems.disks.test' => [
            'driver' => 'local',
            'root' => __DIR__.'/__fixtures__/container',
        ]]);

        $container = (new AssetContainer)->handle('test')->disk('test');

        Facades\AssetContainer::shouldReceive('findByHandle')->andReturn($container);

        return $container;
    }
}
