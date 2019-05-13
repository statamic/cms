<?php

namespace Tests\Stache\Stores;

use Tests\TestCase;
use Statamic\Stache\Stache;
use Illuminate\Filesystem\Filesystem;
use Facades\Statamic\Stache\Traverser;
use Statamic\Stache\Stores\CollectionsStore;
use Statamic\API\Collection as CollectionAPI;
use Statamic\Contracts\Data\Entries\Collection;

class CollectionsStoreTest extends TestCase
{
    function setUp(): void
    {
        parent::setUp();

        mkdir($this->tempDir = __DIR__.'/tmp');

        $stache = (new Stache)->sites(['en']);
        $this->app->instance(Stache::class, $stache);
        $stache->registerStore($this->store = (new CollectionsStore($stache, app('files')))->directory($this->tempDir));
    }

    function tearDown(): void
    {
        parent::tearDown();
        (new Filesystem)->deleteDirectory($this->tempDir);
    }

    /** @test */
    function it_only_gets_top_level_yaml_files()
    {
        touch($this->tempDir.'/one.yaml', 1234567890);
        touch($this->tempDir.'/two.yaml', 1234567890);
        touch($this->tempDir.'/three.txt', 1234567890);
        mkdir($this->tempDir.'/subdirectory');
        touch($this->tempDir.'/subdirectory/nested-one.yaml', 1234567890);
        touch($this->tempDir.'/subdirectory/nested-two.yaml', 1234567890);
        touch($this->tempDir.'/top-level-non-yaml-file.md', 1234567890);

        $files = Traverser::traverse($this->store);

        $this->assertEquals([
            $this->tempDir.'/one.yaml' => 1234567890,
            $this->tempDir.'/two.yaml' => 1234567890,
        ], $files->all());

        // Sanity check. Make sure the file is there but wasn't included.
        $this->assertTrue(file_exists($this->tempDir.'/top-level-non-yaml-file.md'));
    }

    /** @test */
    function it_makes_collection_instances_from_cache()
    {
        $collection = CollectionAPI::create('example');

        $items = $this->store->getItemsFromCache([$collection]);

        $this->assertCount(1, $items);
        $this->assertInstanceOf(Collection::class, reset($items));
    }

    /** @test */
    function it_makes_collection_instances_from_files()
    {
        $item = $this->store->createItemFromFile($this->tempDir.'/example.yaml', "title: Example");

        $this->assertInstanceOf(Collection::class, $item);
        $this->assertEquals('example', $item->handle());
        $this->assertEquals('Example', $item->title());
    }

    /** @test */
    function it_uses_the_filename_as_the_item_key()
    {
        $this->assertEquals(
            'test',
            $this->store->getItemKey('irrelevant', '/path/to/test.yaml')
        );
    }

    /** @test */
    function it_saves_to_disk()
    {
        $this->markTestIncomplete(); // TODO: implementation was changed, tests werent.

        $collection = CollectionAPI::create('new');
        $collection->data([
            'title' => 'New Collection',
            'order' => 'date',
            'foo' => 'bar',
        ]);
        $collection->setEntryPositions(['3' => '123', '10' => '456']);

        $this->store->save($collection);

        $this->assertStringEqualsFile($this->tempDir.'/new.yaml', "title: 'New Collection'\norder: date\nfoo: bar\nentry_order:\n  - '123'\n  - '456'\n");
    }
}
