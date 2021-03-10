<?php

namespace Tests\Assets;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Statamic\Assets\Asset;
use Statamic\Assets\AssetFolder as Folder;
use Statamic\Contracts\Assets\AssetContainer;
use Statamic\Facades;
use Statamic\Filesystem\FlysystemAdapter;
use Tests\TestCase;

class AssetFolderTest extends TestCase
{
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
        $container->shouldReceive('disk')->andReturn($disk = Storage::disk('local'));
        $container->shouldReceive('foldersCacheKey')->andReturn('irrelevant for test');

        $folder = (new Folder)
            ->container($container)
            ->path($path = 'path/to/folder');

        $disk->assertMissing($path);

        $return = $folder->save();

        $this->assertEquals($folder, $return);
        $disk->assertExists($path);
    }

    /** @test */
    public function deleting_a_folder_deletes_the_assets_and_directory()
    {
        Storage::fake('local');
        $container = Facades\AssetContainer::make('test')->disk('local');
        Facades\AssetContainer::shouldReceive('save')->with($container);

        $disk = Storage::disk('local');
        $disk->put('path/to/folder/one.txt', '');
        $disk->put('path/to/sub/folder/two.txt', '');
        $disk->put('path/to/sub/folder/three.txt', '');
        $disk->put('path/to/sub/folder/four.txt', '');
        $disk->put('path/to/sub/folder/subdirectory/five.txt', '');
        $this->assertCount(5, $disk->allFiles());

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

        // TODO: assert about event
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
}
