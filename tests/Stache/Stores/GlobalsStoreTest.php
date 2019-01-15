<?php

namespace Tests\Stache\Stores;

use Mockery;
use Tests\TestCase;
use Statamic\Stache\Stache;
use Illuminate\Filesystem\Filesystem;
use Facades\Statamic\Stache\Traverser;
use Statamic\Stache\Stores\GlobalsStore;
use Statamic\API\GlobalSet as GlobalsAPI;
use Statamic\Contracts\Data\Globals\GlobalSet;

class GlobalsStoreTest extends TestCase
{
    function setUp()
    {
        parent::setUp();

        mkdir($this->tempDir = __DIR__.'/tmp');

        $stache = (new Stache)->sites(['en']);
        $this->store = (new GlobalsStore($stache, app('files')))->directory($this->tempDir);
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
    function it_makes_global_set_instances_from_cache()
    {
        $this->markTestIncomplete(); // it needs to use the toCacheableArray based items instead of instances.

        $set = GlobalsAPI::create('example')->get();

        $items = $this->store->getItemsFromCache([$set]);

        $this->assertCount(1, $items);
        $this->assertInstanceOf(GlobalSet::class, reset($items));
    }

    /** @test */
    function it_makes_global_set_instances_from_files()
    {
        $item = $this->store->createItemFromFile($this->tempDir.'/example.yaml', "id: globals-example\ntitle: Example\nfoo: bar");

        $this->assertInstanceOf(GlobalSet::class, $item);
        $this->assertEquals('globals-example', $item->id());
        $this->assertEquals('example', $item->slug()); // TODO: Change to handle
        $this->assertEquals('Example', $item->title());
        $this->assertEquals(['id' => 'globals-example', 'title' => 'Example', 'foo' => 'bar'], $item->data());
    }

    /** @test */
    function it_uses_the_id_as_the_item_key()
    {
        $set = Mockery::mock();
        $set->shouldReceive('id')->andReturn('123');

        $this->assertEquals(
            '123',
            $this->store->getItemKey($set, '/path/to/irrelevant.yaml')
        );
    }

    /** @test */
    function it_gets_the_id_by_handle()
    {
        $this->markTestIncomplete();// TODO: Revisit once globals have been refactored for multi site

        $this->store->setPath('123', $this->tempDir.'/test.yaml');
        $this->store->setPath('456', $this->tempDir.'/subdirectory/nested.yaml');

        $this->assertEquals('123', $this->store->getIdByHandle('test'));
        $this->assertEquals('456', $this->store->getIdByHandle('nested'));
    }

    /** @test */
    function it_saves_to_disk()
    {
        $global = GlobalsAPI::create('new')
            ->with(['id' => 'id-new', 'foo' => 'bar', 'baz' => 'qux'])
            ->get();

        $global->in('fr')
            ->set('foo', 'le bar')
            ->set('baz', 'qux'); // identical to default to test it doesnt get saved

        $this->store->save($global);

        $this->assertStringEqualsFile(
            $this->tempDir.'/new.yaml',
            "id: id-new\nfoo: bar\nbaz: qux\nfr:\n  foo: 'le bar'\n"
        );
    }
}
