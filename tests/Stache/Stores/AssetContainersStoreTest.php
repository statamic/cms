<?php

namespace Tests\Stache\Stores;

use Facades\Statamic\Stache\Traverser;
use Illuminate\Filesystem\Filesystem;
use Statamic\Assets\Asset;
use Statamic\Contracts\Assets\AssetContainer;
use Statamic\Facades;
use Statamic\Facades\File;
use Statamic\Facades\Path;
use Statamic\Stache\Stache;
use Statamic\Stache\Stores\AssetContainersStore;
use Tests\TestCase;

class AssetContainersStoreTest extends TestCase
{
    function setUp(): void
    {
        parent::setUp();

        mkdir($this->tempDir = __DIR__.'/tmp');

        $stache = (new Stache)->sites(['en']);
        $this->store = (new AssetContainersStore($stache, app('files')))->directory($this->tempDir);
    }

    function tearDown(): void
    {
        parent::tearDown();
        (new Filesystem)->deleteDirectory($this->tempDir);
    }

    /** @test */
    function it_gets_yaml_files()
    {
        touch($this->tempDir.'/one.yaml', 1234567890);
        touch($this->tempDir.'/two.yaml', 1234567890);
        touch($this->tempDir.'/three.txt', 1234567890);
        mkdir($this->tempDir.'/subdirectory');
        touch($this->tempDir.'/subdirectory/nested-one.yaml', 1234567890);
        touch($this->tempDir.'/subdirectory/nested-two.yaml', 1234567890);

        $files = Traverser::filter([$this->store, 'getItemFilter'])->traverse($this->store);

        $dir = Path::tidy($this->tempDir);
        $this->assertEquals([
            $dir.'/one.yaml' => 1234567890,
            $dir.'/two.yaml' => 1234567890,
            $dir.'/subdirectory/nested-one.yaml' => 1234567890,
            $dir.'/subdirectory/nested-two.yaml' => 1234567890,
        ], $files->all());

        // Sanity check. Make sure the file is there but wasn't included.
        $this->assertTrue(file_exists($this->tempDir.'/three.txt'));
    }

    /** @test */
    function it_makes_asset_container_instances_from_files()
    {
        config(['filesystems.disks.test' => ['driver' => 'local', 'root' => __DIR__.'/../../Assets/__fixtures__/container']]);

        Facades\Blueprint::shouldReceive('find')
            ->with('test')->once()
            ->andReturn($blueprint = new \Statamic\Fields\Blueprint);

$contents = <<<EOL
disk: test
title: Example
blueprint: test
EOL;
        $item = $this->store->makeItemFromFile($this->tempDir.'/example.yaml', $contents);

        $this->assertInstanceOf(AssetContainer::class, $item);
        $this->assertEquals(File::disk('test'), $item->disk());
        $this->assertEquals('example', $item->handle());
        $this->assertEquals('Example', $item->title());
        $this->assertEquals($blueprint, $item->blueprint());
        tap($item->assets(), function ($assets) {
            $this->assertEveryItemIsInstanceOf(Asset::class, $assets);
            $this->assertEquals([
                'a.txt' => ['title' => 'File A'],
                'b.txt' => ['title' => 'File B'],
                'nested/nested-a.txt' => ['title' => 'Nested File A'],
                'nested/nested-b.txt' => ['title' => 'Nested File B'],
                'nested/double-nested/double-nested-a.txt' => ['title' => 'Double Nested File A'],
                'nested/double-nested/double-nested-b.txt' => ['title' => 'Double Nested File B'],
            ], $assets->keyBy->path()->map(function ($item) {
                return $item->data()->all();
            })->all());
        });
    }

    /** @test */
    function it_uses_the_handle_as_the_item_key()
    {
        $this->assertEquals(
            'test',
            $this->store->getItemKey(Facades\AssetContainer::make('test'))
        );
    }

    /** @test */
    function it_saves_to_disk()
    {
        Facades\Stache::shouldReceive('store')
            ->with('asset-containers')
            ->andReturn($this->store);

        Facades\Stache::shouldReceive('shouldUpdateIndexes')
            ->andReturnTrue(); // irrelevant for this test but it gets called during saving

        $container = Facades\AssetContainer::make('new')
            ->title('New Container')
            ->blueprint('foo');

        $this->store->save($container);

        $expected = <<<EOT
title: 'New Container'
blueprint: foo

EOT;
        $this->assertStringEqualsFile($this->tempDir.'/new.yaml', $expected);

        $container->allowUploads(false)->createFolders(false)->save();

        $expected = <<<EOT
title: 'New Container'
blueprint: foo
allow_uploads: false
create_folders: false

EOT;
        $this->assertStringEqualsFile($this->tempDir.'/new.yaml', $expected);
    }
}
