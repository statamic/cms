<?php

namespace Tests\Assets;

use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Statamic\Assets\Asset;
use Statamic\Assets\AssetContainerContents;
use Statamic\Assets\AssetFolder as Folder;
use Statamic\Contracts\Assets\AssetContainer;
use Statamic\Events\AssetFolderDeleted;
use Statamic\Events\AssetFolderSaved;
use Statamic\Events\AssetSaved;
use Statamic\Facades;
use Statamic\Filesystem\FlysystemAdapter;
use Tests\TestCase;

class AssetFolderTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        // use the file cache driver so we can test that the cached file listings
        // are coming from the cache and not just the in-memory collection
        config(['cache.default' => 'file']);
        Cache::clear();
    }

    /** @test */
    public function it_gets_and_sets_the_container()
    {
        $folder = new Folder;

        $return = $folder->container($container = Facades\AssetContainer::make('test'));

        $this->assertEquals($folder, $return);
        $this->assertEquals($container, $folder->container());
    }

    /** @test */
    public function it_gets_and_sets_the_path()
    {
        $folder = new Folder;

        $return = $folder->path('path/to/folder');

        $this->assertEquals($folder, $return);
        $this->assertEquals('path/to/folder', $folder->path());
        $this->assertEquals('folder', $folder->basename());
    }

    /** @test */
    public function it_gets_the_disk_from_the_container()
    {
        $container = $this->mock(AssetContainer::class);
        $container->shouldReceive('disk')->andReturn($disk = $this->mock(FlysystemAdapter::class));

        $folder = (new Folder)->container($container);

        $this->assertEquals($disk, $folder->disk());
    }

    /** @test */
    public function the_title_is_the_folder_name()
    {
        $folder = (new Folder)->path('path/to/somewhere');
        $this->assertEquals('somewhere', $folder->title());
    }

    /** @test */
    public function it_gets_the_resolved_path()
    {
        $container = $this->mock(AssetContainer::class);
        $container->shouldReceive('diskPath')->andReturn('path/to/container');

        $folder = (new Folder)->container($container)->path('path/to/folder');

        $this->assertEquals('path/to/container/path/to/folder', $folder->resolvedPath());
    }

    /** @test */
    public function it_gets_assets_in_this_folder()
    {
        $container = $this->mock(AssetContainer::class);
        $container
            ->shouldReceive('assets')
            ->with('path/to/folder', false)
            ->once()
            ->andReturn($assets = collect());

        $folder = (new Folder)
            ->container($container)
            ->path('path/to/folder');

        $this->assertEquals($assets, $folder->assets());
    }

    /** @test */
    public function it_gets_assets_in_this_folder_recursively()
    {
        $container = $this->mock(AssetContainer::class);
        $container
            ->shouldReceive('assets')
            ->with('path/to/folder', true)
            ->once()
            ->andReturn($assets = collect());

        $folder = (new Folder)
            ->container($container)
            ->path('path/to/folder');

        $this->assertEquals($assets, $folder->assets(true));
    }

    /** @test */
    public function it_counts_assets_non_recursively()
    {
        $container = $this->mock(AssetContainer::class);
        $container
            ->shouldReceive('assets')
            ->with('path/to/folder', false)
            ->once()
            ->andReturn(collect([new Asset, new Asset]));

        $folder = (new Folder)
            ->container($container)
            ->path('path/to/folder');

        $this->assertEquals(2, $folder->count());
    }

    /** @test */
    public function it_gets_subfolders_in_this_folder_non_recursively()
    {
        $container = $this->mock(AssetContainer::class);
        $container
            ->shouldReceive('assetFolders')
            ->with('path/to/folder', false)
            ->once()
            ->andReturn(collect([
                (new Folder)->container($container)->path('path/to/folder/one'),
                (new Folder)->container($container)->path('path/to/folder/two'),
            ]));

        $folder = (new Folder)
            ->container($container)
            ->path('path/to/folder');

        $this->assertEquals([
            'path/to/folder/one',
            'path/to/folder/two',
        ], $folder->assetFolders()->map->path()->values()->all());
    }

    /** @test */
    public function it_gets_the_last_modified_date_by_aggregating_all_files()
    {
        Carbon::setTestNow(now());

        $container = $this->mock(AssetContainer::class);
        $container
            ->shouldReceive('assets')
            ->with('path/to/folder', false)
            ->once()
            ->andReturn(collect([
                tap($this->mock(Asset::class), function ($asset) {
                    $asset->shouldReceive('lastModified')->once()->andReturn(Carbon::now()->subMinutes(100));
                }),
                tap($this->mock(Asset::class), function ($asset) {
                    $asset->shouldReceive('lastModified')->once()->andReturn(Carbon::now()->subMinutes(5));
                }),
                tap($this->mock(Asset::class), function ($asset) {
                    $asset->shouldReceive('lastModified')->once()->andReturn(Carbon::now()->subMinutes(200));
                }),
            ]));

        $folder = (new Folder)
            ->container($container)
            ->path('path/to/folder');

        $this->assertEquals(Carbon::now()->subMinutes(5), $folder->lastModified());
    }

    /** @test */
    public function it_creates_directory_when_saving()
    {
        Storage::fake('local');

        $container = $this->mock(AssetContainer::class);
        $container->shouldReceive('contents')->andReturn(new AssetContainerContents($container));
        $container->shouldReceive('disk')->andReturn(new FlysystemAdapter($disk = Storage::disk('local')));
        $container->shouldReceive('foldersCacheKey')->andReturn('irrelevant for test');
        $container->shouldReceive('handle')->andReturn('local');

        $folder = (new Folder)
            ->container($container)
            ->path($path = 'path/to/folder');

        $disk->assertMissing($path);

        $return = $folder->save();

        $this->assertEquals($container->contents()->cached(), $container->contents()->all());
        $this->assertEquals($folder, $return);
        $disk->assertExists($path);
    }

    /** @test */
    public function it_adds_a_gitkeep_file_when_saving()
    {
        Storage::fake('local');

        $container = $this->mock(AssetContainer::class);
        $container->shouldReceive('contents')->andReturn(new AssetContainerContents($container));
        $container->shouldReceive('disk')->andReturn(new FlysystemAdapter($disk = Storage::disk('local')));
        $container->shouldReceive('foldersCacheKey')->andReturn('irrelevant for test');
        $container->shouldReceive('handle')->andReturn('local');

        $folder = (new Folder)
            ->container($container)
            ->path($path = 'path/to/folder');

        $disk->assertMissing($path.'/.gitkeep');

        $folder->save();

        $disk->assertExists($path.'/.gitkeep');
    }

    /** @test */
    public function deleting_a_folder_deletes_the_assets_and_directory()
    {
        $container = $this->containerWithDisk();
        $disk = $container->disk()->filesystem();

        $disk->put('path/to/folder/one.txt', '');
        $disk->put('path/to/sub/folder/two.txt', '');
        $disk->put('path/to/sub/folder/three.txt', '');
        $disk->put('path/to/sub/folder/four.txt', '');
        $disk->put('path/to/sub/folder/subdirectory/five.txt', '');
        $disk->put('path/to/sub/folder/subdirectory/.gitkeep', '');
        $disk->put('path/to/sub/folder/.gitkeep', '');
        $this->assertCount(7, $disk->allFiles());

        $this->assertEquals([
            'path',
            'path/to',
            'path/to/folder',
            'path/to/sub',
            'path/to/sub/folder',
            'path/to/sub/folder/subdirectory',
        ], $container->folders()->all());

        $folder = (new Folder)
            ->container($container)
            ->path('path/to/sub/folder');

        $return = $folder->delete();

        $this->assertEquals($folder, $return);
        $this->assertEquals(['path/to/folder/one.txt'], $disk->allFiles());
        $disk->assertMissing('path/to/sub/folder');

        $this->assertEquals([
            'path',
            'path/to',
            'path/to/folder',
            'path/to/sub',
        ], $container->folders()->all());

        $this->assertEquals([
            'path',
            'path/to',
            'path/to/folder',
            'path/to/folder/one.txt',
            'path/to/sub',
        ], $container->contents()->cached()->keys()->all());

        // TODO: assert about event
    }

    /** @test */
    public function it_can_be_moved_to_another_folder()
    {
        $container = $this->containerWithDisk();
        $disk = $container->disk()->filesystem();

        $paths = collect([
            'move/one.txt',
            'move/two.txt',
            'move/sub/three.txt',
            'move/sub/subsub/four.txt',
            'destination/folder/five.txt',
        ]);

        $paths->each(function ($path) use ($disk, $container) {
            $disk->put($path, '');
            $container->makeAsset($path)->save();
        });

        $paths->each(function ($path) use ($disk) {
            $metaPath = Str::beforeLast($path, '/').'/.meta/'.Str::afterLast($path, '/').'.yaml';
            $disk->assertExists($path);
            $disk->assertExists($metaPath);
        });

        $this->assertCount(10, $disk->allFiles());

        $this->assertEquals([
            'move',
            'move/sub',
            'move/sub/subsub',
            'destination',
            'destination/folder',
        ], $container->folders()->all());

        $folder = (new Folder)
            ->container($container)
            ->path('move');

        Event::fake();

        $folder->move('destination/folder');

        $disk->assertMissing('move');
        $disk->assertMissing('move/sub');
        $disk->assertMissing('move/sub/subsub');

        $disk->assertExists('destination/folder/move');
        $disk->assertExists('destination/folder/move/sub');
        $disk->assertExists('destination/folder/move/sub/subsub');

        $this->assertEquals([
            'destination',
            'destination/folder',
            'destination/folder/move',
            'destination/folder/move/sub',
            'destination/folder/move/sub/subsub',
        ], $container->folders()->all());

        $this->assertEquals([
            'destination',
            'destination/folder',
            'destination/folder/five.txt',
            'destination/folder/move',
            'destination/folder/move/sub',
            'destination/folder/move/sub/subsub',
            'destination/folder/move/sub/subsub/four.txt',
            'destination/folder/move/sub/three.txt',
            'destination/folder/move/one.txt',
            'destination/folder/move/two.txt',
        ], $container->contents()->cached()->keys()->all());

        // Assert asset folder events.
        $paths = ['move', 'move/sub', 'move/sub/subsub'];
        Event::assertDispatchedTimes(AssetFolderDeleted::class, count($paths));
        Event::assertDispatchedTimes(AssetFolderSaved::class, count($paths));
        foreach ($paths as $path) {
            Event::assertDispatched(AssetFolderDeleted::class, function (AssetFolderDeleted $event) use ($path) {
                return $event->folder->path() === $path;
            });

            Event::assertDispatched(AssetFolderSaved::class, function (AssetFolderSaved $event) use ($path) {
                return $event->folder->path() === 'destination/folder/'.$path;
            });
        }

        // Assert asset events.
        $paths = [
            'destination/folder/move/one.txt',
            'destination/folder/move/two.txt',
            'destination/folder/move/sub/three.txt',
            'destination/folder/move/sub/subsub/four.txt',
        ];
        Event::assertDispatchedTimes(AssetSaved::class, count($paths));
        foreach ($paths as $path) {
            Event::assertDispatched(AssetSaved::class, function (AssetSaved $event) use ($path) {
                return $event->asset->path() === $path;
            });
        }
    }

    /** @test */
    public function it_cannot_be_moved_to_its_own_subfolder()
    {
        $container = $this->containerWithDisk();
        $disk = $container->disk()->filesystem();

        $path = 'move/sub/foo.txt';
        $disk->put($path, '');
        $container->makeAsset($path)->save();

        $this->assertCount(2, $disk->allFiles());

        $this->assertEquals([
            'move',
            'move/sub',
        ], $container->folders()->all());

        $folder = (new Folder)
            ->container($container)
            ->path('move');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Folder cannot be moved to its own subfolder.');

        $folder->move('move/sub');

        $this->assertEquals([
            'move',
            'move/sub',
        ], $container->folders()->all());

        $this->assertEquals([
            'move',
            'move/sub',
            'move/sub/foo.txt',
        ], $container->contents()->cached()->keys()->all());
    }

    /** @test */
    public function it_cannot_be_moved_if_the_destination_already_exists()
    {
        $container = $this->containerWithDisk();
        $disk = $container->disk()->filesystem();

        $disk->put($path = 'alfa/foo/one.txt', '');
        $container->makeAsset($path)->save();
        $disk->put($path = 'bravo/foo/two.txt', '');
        $container->makeAsset($path)->save();

        $this->assertCount(4, $disk->allFiles());

        $this->assertEquals([
            'alfa',
            'alfa/foo',
            'bravo',
            'bravo/foo',
        ], $container->folders()->all());

        $folder = (new Folder)
            ->container($container)
            ->path('alfa/foo');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Folder already exists.');

        $folder->move('bravo');

        $this->assertEquals([
            'alfa',
            'alfa/foo',
            'bravo',
            'bravo/foo',
        ], $container->folders()->all());

        $this->assertEquals([
            'alfa',
            'alfa/foo',
            'alfa/foo/one.txt',
            'bravo',
            'bravo/foo',
            'bravo/foo/two.txt',
        ], $container->contents()->cached()->keys()->all());
    }

    /** @test */
    public function it_can_be_renamed()
    {
        $container = $this->containerWithDisk();
        $disk = $container->disk()->filesystem();

        $path = 'before/sub/foo.txt';
        $disk->put($path, '');
        $container->makeAsset($path)->save();

        $this->assertCount(2, $disk->allFiles());

        $this->assertEquals([
            'before',
            'before/sub',
        ], $container->folders()->all());

        $folder = (new Folder)
            ->container($container)
            ->path('before');

        Event::fake();

        $folder->rename('after');

        $disk->assertMissing('before');
        $disk->assertMissing('before/sub');
        $disk->assertMissing('before/sub/foo.txt');

        $disk->assertExists('after');
        $disk->assertExists('after/sub');
        $disk->assertExists('after/sub/foo.txt');

        $this->assertEquals([
            'after',
            'after/sub',
        ], $container->folders()->all());

        $this->assertEquals([
            'after',
            'after/sub',
            'after/sub/foo.txt',
        ], $container->contents()->cached()->keys()->all());

        // Assert asset folder events.
        Event::assertDispatchedTimes(AssetFolderDeleted::class, 2);
        Event::assertDispatched(AssetFolderDeleted::class, function (AssetFolderDeleted $event) {
            return $event->folder->path() === 'before';
        });
        Event::assertDispatched(AssetFolderDeleted::class, function (AssetFolderDeleted $event) {
            return $event->folder->path() === 'before/sub';
        });
        Event::assertDispatchedTimes(AssetFolderSaved::class, 2);
        Event::assertDispatched(AssetFolderSaved::class, function (AssetFolderSaved $event) {
            return $event->folder->path() === 'after';
        });
        Event::assertDispatched(AssetFolderSaved::class, function (AssetFolderSaved $event) {
            return $event->folder->path() === 'after/sub';
        });

        // Assert asset event.
        Event::assertDispatchedTimes(AssetSaved::class, 1);
        Event::assertDispatched(AssetSaved::class, function (AssetSaved $event) {
            return $event->asset->path() === 'after/sub/foo.txt';
        });
    }

    /** @test */
    public function it_cannot_be_renamed_if_the_destination_exists()
    {
        $container = $this->containerWithDisk();
        $disk = $container->disk()->filesystem();

        $disk->put($path = 'alfa/one.txt', '');
        $container->makeAsset($path)->save();
        $disk->put($path = 'bravo/two.txt', '');
        $container->makeAsset($path)->save();

        $this->assertCount(4, $disk->allFiles());

        $this->assertEquals([
            'alfa',
            'bravo',
        ], $container->folders()->all());

        $folder = (new Folder)
            ->container($container)
            ->path('alfa');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Folder already exists.');

        $folder->rename('bravo');

        $this->assertEquals([
            'alfa',
            'bravo',
        ], $container->folders()->all());

        $this->assertEquals([
            'alfa',
            'alfa/one.txt',
            'bravo',
            'bravo/two.txt',
        ], $container->contents()->cached()->keys()->all());
    }

    /** @test */
    public function it_gets_the_parent_folder()
    {
        $container = $this->mock(AssetContainer::class);
        $expectedParent = (new Folder)
            ->container($container)
            ->path('grandparent/parent');
        $container
            ->shouldReceive('assetFolder')->once()
            ->with('grandparent/parent')
            ->andReturn($expectedParent);

        $folder = (new Folder)
            ->container($container)
            ->path('grandparent/parent/child');

        $parent = $folder->parent();

        $this->assertInstanceOf(Folder::class, $parent);
        $this->assertNotEquals($folder, $parent);
    }

    /** @test */
    public function the_root_has_no_parent()
    {
        $folder = (new Folder)
            ->container($this->mock(AssetContainer::class))
            ->path('/');

        $this->assertNull($folder->parent());
    }

    /** @test */
    public function it_converts_to_an_array()
    {
        $container = $this->mock(AssetContainer::class);
        $container
            ->shouldReceive('assetFolder')
            ->with('grandparent/parent')
            ->andReturn((new Folder)->container($container)->path('grandparent/parent'));

        $folder = (new Folder)
            ->container($container)
            ->path('grandparent/parent/child');

        $this->assertEquals([
            'title' => 'child',
            'path' => 'grandparent/parent/child',
            'parent_path' => 'grandparent/parent',
            'basename' => 'child',
        ], $folder->toArray());
    }

    private function containerWithDisk()
    {
        Storage::fake('local');

        $container = Facades\AssetContainer::make('test')->disk('local');
        Facades\AssetContainer::shouldReceive('findByHandle')->with('test')->andReturn($container);
        Facades\AssetContainer::shouldReceive('save')->with($container);

        return $container;
    }
}
