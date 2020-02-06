<?php

namespace Tests\Assets;

use Statamic\Facades;
use Tests\TestCase;
use Statamic\Assets\Asset;
use Statamic\Fields\Blueprint;
use Illuminate\Support\Collection;
use Statamic\Assets\AssetContainer;
use Illuminate\Support\Facades\Storage;
use Statamic\Filesystem\FlysystemAdapter;
use Tests\PreventSavingStacheItemsToDisk;
use Statamic\Contracts\Assets\AssetFolder;
use Facades\Statamic\Fields\BlueprintRepository;
use Statamic\Contracts\Assets\Asset as AssetContract;

class AssetContainerTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
    function it_gets_and_sets_the_id()
    {
        $container = new AssetContainer;
        $this->assertNull($container->id());

        $return = $container->id('123');

        $this->assertEquals($container, $return);
        $this->assertEquals('123', $container->id());
    }

    /** @test */
    function it_gets_and_sets_the_handle()
    {
        $container = new AssetContainer;
        $this->assertNull($container->handle());

        $return = $container->handle('123');

        $this->assertEquals($container, $return);
        $this->assertEquals('123', $container->handle());
    }

    /** @test */
    function it_changes_the_handle_when_changing_the_id()
    {
        // only applies to a file implementation

        $container = (new AssetContainer)->handle('handle');
        $container->id('id');
        $this->assertEquals('id', $container->handle());
    }

    /** @test */
    function it_changes_the_id_when_changing_the_handle()
    {
        // only applies to a file implementation

        $container = (new AssetContainer)->id('id');
        $container->handle('handle');
        $this->assertEquals('handle', $container->id());
    }

    /** @test */
    function it_gets_and_sets_the_disk()
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
    function it_gets_the_url_from_the_disk_config()
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
    function it_gets_the_url_from_the_disk_config_when_its_relative()
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
    function its_private_if_the_disk_has_no_url()
    {
        Storage::fake('test');

        $container = (new AssetContainer)->disk('test');
        $this->assertTrue($container->private());
        $this->assertFalse($container->accessible());

        Storage::fake('test', ['url' => '/url']);

        $this->assertFalse($container->private());
        $this->assertTrue($container->accessible());
    }

    /** @test */
    function it_gets_and_sets_the_title()
    {
        $container = (new AssetContainer)->handle('main');
        $this->assertEquals('Main', $container->title());

        $return = $container->title('Main Assets');

        $this->assertEquals($container, $return);
        $this->assertEquals('Main Assets', $container->title());
    }

    /** @test */
    function it_gets_and_sets_blueprint()
    {
        BlueprintRepository::shouldReceive('find')
            ->with('asset')
            ->andReturn($defaultBlueprint = new Blueprint);

        BlueprintRepository::shouldReceive('find')
            ->with('custom')
            ->andReturn($customBlueprint = new Blueprint);

        $container = new AssetContainer;
        $this->assertEquals($defaultBlueprint, $container->blueprint());

        $return = $container->blueprint('custom');

        $this->assertEquals($container, $return);
        $this->assertEquals($customBlueprint, $container->blueprint());
    }

    /** @test */
    function it_gets_and_sets_whether_uploads_are_allowed()
    {
        $container = new AssetContainer;
        $this->assertTrue($container->allowUploads());

        $return = $container->allowUploads(false);

        $this->assertEquals($container, $return);
        $this->assertFalse($container->allowUploads());
    }

    /** @test */
    function it_gets_and_sets_whether_folders_can_be_created()
    {
        $container = new AssetContainer;
        $this->assertTrue($container->createFolders());

        $return = $container->createFolders(false);

        $this->assertEquals($container, $return);
        $this->assertFalse($container->createFolders());
    }

    /** @test */
    function it_saves_the_container_through_the_api()
    {
        Facades\AssetContainer::spy();

        $container = new AssetContainer;

        $return = $container->save();

        $this->assertEquals($container, $return);
        Facades\AssetContainer::shouldHaveReceived('save')->with($container)->once();
    }

    /** @test */
    function it_gets_the_path_from_the_stache()
    {
        $container = (new AssetContainer)->handle('test');

        $this->assertEquals($this->fakeStacheDirectory.'/content/assets/test.yaml', $container->path());
    }

    /** @test */
    function it_gets_all_files_by_default()
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
    function it_gets_files_in_a_folder()
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
    function it_gets_files_in_a_folder_recursively()
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
    function it_gets_all_folders_by_default()
    {
        $this->assertEquals([
            'nested',
            'nested/double-nested',
        ], $this->containerWithDisk()->folders()->all());
    }

    /** @test */
    function it_gets_folders_in_given_folder()
    {
        $this->assertEquals([
            'nested',
        ], $this->containerWithDisk()->folders('/')->all());

        $this->assertEquals([
            'nested/double-nested',
        ], $this->containerWithDisk()->folders('nested')->all());
    }

    /** @test */
    function it_gets_folders_in_given_folder_recursively()
    {
        $this->assertEquals([
            'nested',
            'nested/double-nested',
        ], $this->containerWithDisk()->folders('/', true)->all());

        $this->assertEquals([
            'nested/double-nested',
        ], $this->containerWithDisk()->folders('nested', true)->all());
    }

    /** @test */
    function it_gets_an_asset()
    {
        $asset = $this->containerWithDisk()->asset('a.txt');

        $this->assertInstanceOf(AssetContract::class, $asset);
    }

    /** @test */
    function it_gets_an_asset_with_data()
    {
        $container = $this->containerWithDisk();

        tap($container->asset('a.txt'), function ($asset) {
            $this->assertInstanceOf(AssetContract::class, $asset);
            $this->assertEquals('File A', $asset->get('title'));
        });

        $this->assertNull($container->asset('non-existent.txt'));
    }

    /** @test */
    function it_makes_an_asset_at_given_path()
    {
        $container = new AssetContainer;
        $asset = $container->makeAsset('path/to/test.txt');

        $this->assertInstanceOf(Asset::class, $asset);
        $this->assertEquals($container, $asset->container());
        $this->assertEquals('path/to/test.txt', $asset->path());
    }

    /** @test */
    function it_gets_all_assets_by_default()
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
    function it_gets_assets_in_a_folder()
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
    function it_gets_assets_in_a_folder_recursively()
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
    function it_gets_an_asset_folder()
    {
        Storage::fake('test');
        $container = $this->containerWithDisk();

        $folder = $container->assetFolder('foo');

        $this->assertInstanceOf(AssetFolder::class, $folder);
        $this->assertEquals('foo', $folder->title());
        $this->assertEquals('foo', $folder->path());
        $this->assertEquals($container, $folder->container());

        Storage::disk('test')->put('foo/folder.yaml', "title: 'Test Folder'");
        $folder = $container->assetFolder('foo');

        $this->assertEquals('Test Folder', $folder->title());
    }

    private function containerWithDisk()
    {
        config(['filesystems.disks.test' => [
            'driver' => 'local',
            'root' => __DIR__.'/__fixtures__/container',
        ]]);

        return (new AssetContainer)->handle('test')->disk('test');
    }
}
