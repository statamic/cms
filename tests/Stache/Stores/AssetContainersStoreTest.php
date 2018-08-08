<?php

namespace Tests\Stache\Stores;

use Tests\TestCase;
use Statamic\Stache\Stache;
use Illuminate\Filesystem\Filesystem;
use Facades\Statamic\Stache\Traverser;
use Statamic\Contracts\Assets\AssetContainer;
use Statamic\Stache\Stores\AssetContainersStore;
use Statamic\API\AssetContainer as AssetContainerAPI;

class AssetContainersStoreTest extends TestCase
{
    function setUp()
    {
        parent::setUp();

        mkdir($this->tempDir = __DIR__.'/tmp');

        $stache = (new Stache)->sites(['en']);
        $this->store = (new AssetContainersStore($stache))->directory($this->tempDir);
    }

    function tearDown()
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

        $files = Traverser::traverse($this->store);

        $this->assertEquals([
            $this->tempDir.'/one.yaml' => 1234567890,
            $this->tempDir.'/two.yaml' => 1234567890,
            $this->tempDir.'/subdirectory/nested-one.yaml' => 1234567890,
            $this->tempDir.'/subdirectory/nested-two.yaml' => 1234567890,
        ], $files->all());

        // Sanity check. Make sure the file is there but wasn't included.
        $this->assertTrue(file_exists($this->tempDir.'/three.txt'));
    }

    /** @test */
    function it_makes_asset_container_instances_from_cache()
    {
        $container = AssetContainerAPI::create();

        $items = $this->store->getItemsFromCache([$container]);

        $this->assertCount(1, $items);
        $this->assertInstanceOf(AssetContainer::class, reset($items));
    }

    /** @test */
    function it_makes_asset_container_instances_from_files()
    {
        $item = $this->store->createItemFromFile($this->tempDir.'/example.yaml', "title: Example\nfoo: bar");

        $this->assertInstanceOf(AssetContainer::class, $item);
        $this->assertEquals('example', $item->handle());
        $this->assertEquals('Example', $item->title());
        $this->assertEquals(['title' => 'Example', 'foo' => 'bar'], $item->data());
    }

    /** @test */
    function it_uses_the_filename_as_the_item_key()
    {
        $this->assertEquals(
            'test',
            $this->store->getItemKey('irrelevant', '/path/to/test.yaml')
        );
    }
}
