<?php

namespace Tests\Assets;

use Facades\Statamic\Fields\BlueprintRepository;
use Illuminate\Cache\Events\CacheHit;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\DirectoryAttributes;
use League\Flysystem\DirectoryListing;
use League\Flysystem\FileAttributes;
use League\Flysystem\PathTraversalDetected;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Assets\Asset;
use Statamic\Assets\AssetContainer;
use Statamic\Contracts\Assets\Asset as AssetContract;
use Statamic\Contracts\Assets\AssetFolder;
use Statamic\Events\AssetContainerCreated;
use Statamic\Events\AssetContainerCreating;
use Statamic\Events\AssetContainerDeleted;
use Statamic\Events\AssetContainerDeleting;
use Statamic\Events\AssetContainerSaved;
use Statamic\Events\AssetContainerSaving;
use Statamic\Facades;
use Statamic\Facades\Blink;
use Statamic\Facades\File;
use Statamic\Fields\Blueprint;
use Statamic\Filesystem\Filesystem;
use Statamic\Filesystem\FlysystemAdapter;
use Tests\Fakes\FakeArtisanRequest;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class AssetContainerTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_gets_and_sets_the_id()
    {
        $container = new AssetContainer;
        $this->assertNull($container->id());

        $return = $container->id('123');

        $this->assertEquals($container, $return);
        $this->assertEquals('123', $container->id());
    }

    #[Test]
    public function it_gets_and_sets_the_handle()
    {
        $container = new AssetContainer;
        $this->assertNull($container->handle());

        $return = $container->handle('123');

        $this->assertEquals($container, $return);
        $this->assertEquals('123', $container->handle());
    }

    #[Test]
    public function it_changes_the_handle_when_changing_the_id()
    {
        // only applies to a file implementation

        $container = (new AssetContainer)->handle('handle');
        $container->id('id');
        $this->assertEquals('id', $container->handle());
    }

    #[Test]
    public function it_changes_the_id_when_changing_the_handle()
    {
        // only applies to a file implementation

        $container = (new AssetContainer)->id('id');
        $container->handle('handle');
        $this->assertEquals('handle', $container->id());
    }

    #[Test]
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

        $config = $container->disk()->filesystem()->getConfig();

        $this->assertEquals($container, $return);
        $this->assertInstanceOf(FlysystemAdapter::class, $container->disk());
        $this->assertEquals('test', $container->diskHandle());
        $this->assertEquals('/the-url', $config['url']);
    }

    #[Test]
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

    #[Test]
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

    #[Test]
    public function it_gets_the_url_from_the_disk_config_when_its_app_url()
    {
        config(['filesystems.disks.test' => [
            'driver' => 'local',
            'root' => __DIR__.'/__fixtures__/container',
            'url' => 'http://localhost/container',
        ]]);

        $container = (new AssetContainer)->disk('test');

        $this->assertEquals('/container', $container->url());
        $this->assertEquals('http://localhost/container', $container->absoluteUrl());
    }

    #[Test]
    public function its_private_if_the_disk_has_no_url()
    {
        Storage::fake('test');

        $container = (new AssetContainer)->disk('test');
        $this->assertTrue($container->private());
        $this->assertFalse($container->accessible());
        $this->assertNull($container->url());

        Storage::fake('test', ['url' => '/url']);

        $this->assertFalse($container->private());
        $this->assertTrue($container->accessible());
        $this->assertEquals('/url', $container->url());
    }

    #[Test]
    public function it_gets_and_sets_the_title()
    {
        $container = (new AssetContainer)->handle('main');
        $this->assertEquals('Main', $container->title());

        $return = $container->title('Main Assets');

        $this->assertEquals($container, $return);
        $this->assertEquals('Main Assets', $container->title());
    }

    #[Test]
    public function it_gets_the_blueprint()
    {
        BlueprintRepository::shouldReceive('find')->with('assets/main')->andReturn($blueprint = new Blueprint);

        $container = (new AssetContainer)->handle('main');
        $this->assertEquals($blueprint, $container->blueprint());
    }

    #[Test]
    public function it_gets_and_sets_whether_uploads_are_allowed()
    {
        $container = new AssetContainer;
        $this->assertTrue($container->allowUploads());

        $return = $container->allowUploads(false);

        $this->assertEquals($container, $return);
        $this->assertFalse($container->allowUploads());
    }

    #[Test]
    public function it_gets_and_sets_whether_folders_can_be_created()
    {
        $container = new AssetContainer;
        $this->assertTrue($container->createFolders());

        $return = $container->createFolders(false);

        $this->assertEquals($container, $return);
        $this->assertFalse($container->createFolders());
    }

    #[Test]
    public function it_gets_and_sets_whether_renaming_is_allowed()
    {
        $container = new AssetContainer;
        $this->assertTrue($container->allowRenaming());

        $return = $container->allowRenaming(false);

        $this->assertEquals($container, $return);
        $this->assertFalse($container->allowRenaming());
    }

    #[Test]
    public function it_gets_and_sets_whether_moving_is_allowed()
    {
        $container = new AssetContainer;
        $this->assertTrue($container->allowMoving());

        $return = $container->allowMoving(false);

        $this->assertEquals($container, $return);
        $this->assertFalse($container->allowMoving());
    }

    #[Test]
    public function it_gets_and_sets_whether_downloading_is_allowed()
    {
        $container = new AssetContainer;
        $this->assertTrue($container->allowDownloading());

        $return = $container->allowDownloading(false);

        $this->assertEquals($container, $return);
        $this->assertFalse($container->allowDownloading());
    }

    #[Test]
    public function it_gets_and_sets_the_validation_rules()
    {
        $container = new AssetContainer;
        $this->assertEmpty($container->validationRules());

        $return = $container->validationRules(['max:5120']);

        $this->assertEquals($container, $return);
        $this->assertEquals(['max:5120'], $container->validationRules());
    }

    #[Test]
    public function it_gets_and_sets_glide_source_preset_for_upload_processing()
    {
        $container = new AssetContainer;
        $this->assertNull($container->sourcePreset());

        $return = $container->sourcePreset('watermarked');

        $this->assertEquals($container, $return);
        $this->assertEquals('watermarked', $container->sourcePreset());
    }

    #[Test]
    #[DataProvider('warmPresetProvider')]
    public function it_defines_which_presets_to_warm($source, $presets, $expectedIntelligent, $expectedWarm)
    {
        config(['statamic.assets.image_manipulation.presets' => [
            'small' => ['w' => '15', 'h' => '15'],
            'medium' => ['w' => '500', 'h' => '500'],
            'large' => ['w' => '1000', 'h' => '1000'],
            'max' => ['w' => '3000', 'h' => '3000', 'mark' => 'watermark.jpg'],
        ]]);

        $container = (new AssetContainer)
            ->sourcePreset($source)
            ->warmPresets($presets);

        $this->assertEquals($expectedIntelligent, $container->warmsPresetsIntelligently());
        $this->assertEquals($expectedWarm, $container->warmPresets());
    }

    public static function warmPresetProvider()
    {
        return [
            'no source, no presets' => [null, null, true, ['small', 'medium', 'large', 'max']],
            'no source, with presets' => [null, ['small', 'medium'], false, ['small', 'medium']],
            'with source, no presets' => ['max', null, true, ['small', 'medium', 'large']],
            'with source, with presets' => ['max', ['small'], false, ['small']],
            'with source, with presets, including source' => ['max', ['small', 'max'], false, ['small', 'max']],
            'no source, presets false' => [null, false, false, []],
            'with source, presets false' => ['max', false, false, []],
        ];
    }

    #[Test]
    public function it_saves_the_container_through_the_api()
    {
        Event::fake();
        Facades\AssetContainer::spy();

        $container = new AssetContainer;

        $return = $container->save();

        $this->assertEquals($container, $return);

        Event::assertDispatched(AssetContainerCreating::class, function ($event) use ($container) {
            return $event->container === $container;
        });

        Event::assertDispatched(AssetContainerSaving::class, function ($event) use ($container) {
            return $event->container === $container;
        });

        Event::assertDispatched(AssetContainerCreated::class, function ($event) use ($container) {
            return $event->container === $container;
        });

        Event::assertDispatched(AssetContainerSaved::class, function ($event) use ($container) {
            return $event->container === $container;
        });
    }

    #[Test]
    public function it_dispatches_asset_container_created_only_once()
    {
        Event::fake();
        Facades\AssetContainer::spy();

        $container = new AssetContainer;

        Facades\AssetContainer::shouldReceive('save')->with($container);
        Facades\AssetContainer::shouldReceive('find')->with($container->handle())->times(3)->andReturn(null, $container, $container);

        $container->save();
        $container->save();
        $container->save();

        Event::assertDispatched(AssetContainerSaved::class, 3);
        Event::assertDispatched(AssetContainerCreated::class, 1);
    }

    #[Test]
    public function it_saves_quietly()
    {
        Event::fake();
        Facades\AssetContainer::spy();

        $container = new AssetContainer;

        $return = $container->saveQuietly();

        $this->assertEquals($container, $return);

        Event::assertNotDispatched(AssetContainerCreating::class);
        Event::assertNotDispatched(AssetContainerSaving::class);
        Event::assertNotDispatched(AssetContainerSaved::class);
        Event::assertNotDispatched(AssetContainerCreated::class);
    }

    #[Test]
    public function if_creating_event_returns_false_the_asset_container_doesnt_save()
    {
        Event::fake([AssetContainerCreated::class]);
        Facades\AssetContainer::spy();

        Event::listen(AssetContainerCreating::class, function () {
            return false;
        });

        $container = new AssetContainer;

        $return = $container->save();

        $this->assertFalse($return);

        Event::assertNotDispatched(AssetContainerCreated::class);
    }

    #[Test]
    public function if_saving_event_returns_false_the_asset_container_doesnt_save()
    {
        Event::fake([AssetContainerSaved::class]);
        Facades\AssetContainer::spy();

        Event::listen(AssetContainerSaving::class, function () {
            return false;
        });

        $container = new AssetContainer;

        $return = $container->saveQuietly();

        $this->assertEquals($container, $return);

        Event::assertNotDispatched(AssetContainerSaved::class);
    }

    #[Test]
    public function it_gets_the_path_from_the_stache()
    {
        $container = (new AssetContainer)->handle('test');

        $this->assertEquals($this->fakeStacheDirectory.'/content/assets/test.yaml', $container->path());
    }

    #[Test]
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

    #[Test]
    public function it_gets_all_meta_files_by_default()
    {
        $this->assertEquals([
            '.meta/a.txt.yaml',
            '.meta/b.txt.yaml',
            'nested/.meta/nested-a.txt.yaml',
            'nested/.meta/nested-b.txt.yaml',
            'nested/double-nested/.meta/double-nested-a.txt.yaml',
            'nested/double-nested/.meta/double-nested-b.txt.yaml',
        ], $this->containerWithDisk()->metaFiles()->all());
    }

    #[Test]
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

    #[Test]
    public function it_gets_meta_files_in_a_folder()
    {
        $this->assertEquals([
            '.meta/a.txt.yaml',
            '.meta/b.txt.yaml',
        ], $this->containerWithDisk()->metaFiles('/')->all());

        $this->assertEquals([
            'nested/.meta/nested-a.txt.yaml',
            'nested/.meta/nested-b.txt.yaml',
        ], $this->containerWithDisk()->metaFiles('nested')->all());
    }

    #[Test]
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

    #[Test]
    public function it_gets_meta_files_in_a_folder_recursively()
    {
        $this->assertEquals([
            '.meta/a.txt.yaml',
            '.meta/b.txt.yaml',
            'nested/.meta/nested-a.txt.yaml',
            'nested/.meta/nested-b.txt.yaml',
            'nested/double-nested/.meta/double-nested-a.txt.yaml',
            'nested/double-nested/.meta/double-nested-b.txt.yaml',
        ], $this->containerWithDisk()->metaFiles('/', true)->all());

        $this->assertEquals([
            'nested/.meta/nested-a.txt.yaml',
            'nested/.meta/nested-b.txt.yaml',
            'nested/double-nested/.meta/double-nested-a.txt.yaml',
            'nested/double-nested/.meta/double-nested-b.txt.yaml',
        ], $this->containerWithDisk()->metaFiles('nested', true)->all());
    }

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
    public function it_gets_the_files_from_the_filesystem_only_once()
    {
        Carbon::setTestNow(now()->startOfMinute());

        $disk = $this->mock(Filesystem::class);
        $disk->shouldReceive('filesystem->getDriver->listContents')
            ->with('/', true)
            ->once()
            ->andReturn(new DirectoryListing([
                new FileAttributes('.meta/one.jpg.yaml'),
                new FileAttributes('.DS_Store'),
                new FileAttributes('.gitignore'),
                new FileAttributes('one.jpg'),
                new FileAttributes('two.jpg'),
            ]));

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

    #[Test]
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

    #[Test]
    public function it_gets_the_files_from_the_cache_every_time_if_running_in_a_queue_worker()
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

        Request::swap(new FakeArtisanRequest('queue:listen'));

        $expected = ['one.jpg', 'two.jpg'];
        $this->assertEquals($expected, $container->files()->all());
        $this->assertEquals(1, $cacheHits);
        $this->assertEquals($expected, $container->files()->all());
        $this->assertEquals(2, $cacheHits);
    }

    #[Test]
    public function it_gets_the_folders_from_the_filesystem_only_once()
    {
        Carbon::setTestNow(now()->startOfMinute());

        $disk = $this->mock(Filesystem::class);
        $disk->shouldReceive('filesystem->getDriver->listContents')
            ->with('/', true)
            ->once()
            ->andReturn(new DirectoryListing([
                new DirectoryAttributes('.meta'),
                new DirectoryAttributes('one'),
                new DirectoryAttributes('one/.meta'),
                new DirectoryAttributes('two'),
            ]));

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

    #[Test]
    public function it_gets_the_folders_even_if_some_folders_are_missing()
    {
        // For example, S3 may not not return a directory as part of the listing in
        // some situations, even though there may be a file in those directories.

        $disk = $this->mock(Filesystem::class);
        $disk->shouldReceive('filesystem->getDriver->listContents')
            ->with('/', true)
            ->once()
            ->andReturn(new DirectoryListing([
                new DirectoryAttributes('alfa'),
                new DirectoryAttributes('bravo'),
                new FileAttributes('charlie/delta/echo/foxtrot.jpg'),
                new FileAttributes('golf.jpg'),
            ]));

        File::shouldReceive('disk')->with('test')->andReturn($disk);

        $this->assertFalse(Cache::has($cacheKey = 'asset-list-contents-test'));
        $this->assertFalse(Blink::has($cacheKey));

        $container = (new AssetContainer)->handle('test')->disk('test');

        $expected = ['alfa', 'bravo', 'charlie', 'charlie/delta', 'charlie/delta/echo'];
        $this->assertEquals($expected, $container->folders()->all());
    }

    #[Test]
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

    #[Test]
    public function it_gets_the_folders_from_the_cache_and_blink_every_time_if_running_in_a_queue_worker()
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

        Request::swap(new FakeArtisanRequest('queue:listen'));

        $expected = ['one', 'two'];
        $this->assertEquals($expected, $container->folders()->all());
        $this->assertEquals(1, $cacheHits);
        $this->assertEquals($expected, $container->folders()->all());
        $this->assertEquals(2, $cacheHits);

        $anotherInstanceOfTheContainer = (new AssetContainer)->handle('test')->disk('test');
        $this->assertEquals($expected, $anotherInstanceOfTheContainer->folders()->all());
        $this->assertEquals(3, $cacheHits);
    }

    #[Test]
    public function it_gets_an_asset()
    {
        $asset = $this->containerWithDisk()->asset('a.txt');

        $this->assertInstanceOf(AssetContract::class, $asset);
    }

    #[Test]
    public function it_gets_an_asset_with_data()
    {
        $container = $this->containerWithDisk();

        tap($container->asset('a.txt'), function ($asset) {
            $this->assertInstanceOf(AssetContract::class, $asset);
            $this->assertEquals('File A', $asset->get('title'));
        });

        $this->assertNull($container->asset('non-existent.txt'));
    }

    #[Test]
    public function it_makes_an_asset_at_given_path()
    {
        $container = $this->containerWithDisk();
        $asset = $container->makeAsset('path/to/test.txt');

        $this->assertInstanceOf(Asset::class, $asset);
        $this->assertEquals($container, $asset->container());
        $this->assertEquals('path/to/test.txt', $asset->path());
    }

    #[Test]
    public function it_cannot_make_an_asset_using_path_traversal()
    {
        $this->expectException(PathTraversalDetected::class);
        $this->expectExceptionMessage('Path traversal detected: foo/../test.txt');

        $container = $this->containerWithDisk();
        $container->makeAsset('foo/../test.txt');
    }

    #[Test]
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

    #[Test]
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

    #[Test]
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

    /**
     * @see https://github.com/statamic/cms/issues/8825
     * @see https://github.com/statamic/cms/pull/8826
     **/
    #[Test]
    public function it_doesnt_get_kebab_case_folder_assets_when_querying_snake_case_folder()
    {
        tap($this->containerWithDisk('snake-kebab')->assets('foo_bar', true), function ($assets) {
            $this->assertCount(1, $assets);
            $this->assertEquals('foo_bar/alfa.txt', $assets->first()->path());
        });
    }

    /**
     * @see https://github.com/statamic/cms/issues/5405
     * @see https://github.com/statamic/cms/pull/5433
     **/
    #[Test]
    public function it_can_get_assets_in_a_folder_named_zero()
    {
        $container = $this->containerWithDisk();

        $container->disk()->delete('0');

        $paths = [
            '0/a.txt',
            '0/b.txt',
            '0/nested/c.txt',
            '0/nested/d.txt',
            '0/nested/e.txt',
        ];

        foreach ($paths as $path) {
            $container->disk()->put($path, 'test');
        }

        tap($container->assets('0'), function ($assets) {
            $this->assertInstanceOf(Collection::class, $assets);
            $this->assertEquals([
                '0/a.txt', '0/b.txt',
            ], $assets->map->path()->values()->all());
        });

        tap($container->assets('0', true), function ($assets) {
            $this->assertInstanceOf(Collection::class, $assets);
            $this->assertEquals([
                '0/a.txt', '0/b.txt',
                '0/nested/c.txt', '0/nested/d.txt', '0/nested/e.txt',
            ], $assets->map->path()->values()->all());
        });

        $container->disk()->delete('0');
    }

    /**
     * @see https://github.com/statamic/cms/issues/5405
     * @see https://github.com/statamic/cms/pull/5433
     **/
    #[Test]
    public function it_wont_get_assets_that_share_a_similar_folder_prefix()
    {
        $container = $this->containerWithDisk();

        $container->disk()->delete('test');

        $paths = [
            'test/cat/siamese.jpg',
            'test/cat/tabby.jpg',
            'test/cat/cartoon/cheshire.jpg',
            'test/categories/favorite.jpg',
            'test/categories/non-favorite.jpg',
        ];

        foreach ($paths as $path) {
            $container->disk()->put($path, 'test');
        }

        tap($container->assets('test/cat'), function ($assets) {
            $this->assertInstanceOf(Collection::class, $assets);
            $this->assertEquals([
                'test/cat/siamese.jpg',
                'test/cat/tabby.jpg',
            ], $assets->map->path()->values()->all());
        });

        tap($container->assets('test/cat', true), function ($assets) {
            $this->assertInstanceOf(Collection::class, $assets);
            $this->assertEquals([
                'test/cat/cartoon/cheshire.jpg',
                'test/cat/siamese.jpg',
                'test/cat/tabby.jpg',
            ], $assets->map->path()->values()->all());
        });

        $container->disk()->delete('test');
    }

    #[Test]
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

    #[Test]
    public function it_gets_evaluated_augmented_value_using_magic_property()
    {
        $container = $this->containerWithDisk();

        $container
            ->toAugmentedCollection()
            ->except(['assets'])
            ->each(fn ($value, $key) => $this->assertEquals($value->value(), $container->{$key}))
            ->each(fn ($value, $key) => $this->assertEquals($value->value(), $container[$key]));
    }

    #[Test]
    public function it_is_arrayable()
    {
        $container = $this->containerWithDisk();

        $this->assertInstanceOf(Arrayable::class, $container);

        $expectedAugmented = $container->toAugmentedCollection()->except('assets');

        $array = $container->toArray();

        $this->assertCount($expectedAugmented->count(), $array);

        collect($array)
            ->each(function ($value, $key) use ($container) {
                $expected = $container->{$key};
                $expected = $expected instanceof Arrayable ? $expected->toArray() : $expected;
                $this->assertEquals($expected, $value);
            });
    }

    #[Test]
    public function it_fires_events_when_deleting()
    {
        Event::fake();

        Storage::fake('test');

        $container = Facades\AssetContainer::make('test')->disk('test');
        $container->save();

        $return = $container->delete();

        Event::assertDispatched(AssetContainerDeleted::class);
        Event::assertDispatched(AssetContainerDeleting::class);

        $this->assertTrue($return);
    }

    #[Test]
    public function it_deletes_quietly()
    {
        Event::fake();

        Storage::fake('test');

        $container = Facades\AssetContainer::make('test')->disk('test');
        $container->save();

        $return = $container->deleteQuietly();

        Event::assertNotDispatched(AssetContainerDeleted::class);
        Event::assertNotDispatched(AssetContainerDeleting::class);

        $this->assertTrue($return);
    }

    #[Test]
    public function it_does_not_delete_when_a_deleting_event_returns_false()
    {
        Event::fake([AssetContainerDeleted::class]);

        Event::listen(AssetContainerDeleting::class, function () {
            return false;
        });

        Storage::fake('test');

        $container = Facades\AssetContainer::make('test');
        $container->save();

        $return = $container->delete();

        $this->assertFalse($return);
        Event::assertNotDispatched(AssetContainerDeleted::class);
    }

    private function containerWithDisk($fixture = 'container')
    {
        config(['filesystems.disks.test' => [
            'driver' => 'local',
            'root' => __DIR__.'/__fixtures__/'.$fixture,
        ]]);

        $container = (new AssetContainer)->handle('test')->disk('test');

        Facades\AssetContainer::shouldReceive('findByHandle')->andReturn($container);

        return $container;
    }
}
