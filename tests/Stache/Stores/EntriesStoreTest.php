<?php

namespace Tests\Stache\Stores;

use Mockery;
use Tests\TestCase;
use Statamic\Stache\Stache;
use Facades\Statamic\Stache\Traverser;
use Statamic\Stache\Stores\EntriesStore;

class EntriesStoreTest extends TestCase
{
    function setUp()
    {
        parent::setUp();

        $stache = (new Stache)->sites(['en']);
        $this->store = (new EntriesStore($stache))
            ->directory($this->directory = __DIR__.'/../__fixtures__/content/collections');
    }

    /** @test */
    function it_gets_nested_files()
    {
        $files = Traverser::traverse($this->store);

        $this->assertEquals(collect([
            $this->directory.'/alphabetical/alpha.md',
            $this->directory.'/alphabetical/bravo.md',
            $this->directory.'/alphabetical/zulu.md',
            $this->directory.'/blog/2017-25-12.christmas.md',
            $this->directory.'/blog/2018-07-04.fourth-of-july.md',
            $this->directory.'/numeric/1.one.md',
            $this->directory.'/numeric/2.two.md',
            $this->directory.'/numeric/3.three.md',
            $this->directory.'/pages/about.md',
            $this->directory.'/pages/about/board.md',
            $this->directory.'/pages/about/directors.md',
            $this->directory.'/pages/blog.md',
            $this->directory.'/pages/contact.md',
            $this->directory.'/pages/home.md',
        ])->sort()->values()->all(), $files->keys()->sort()->values()->all());

        // Sanity check. These files should exist but not be included.
        $this->assertTrue(file_exists($this->directory.'/blog.yaml'));
        $this->assertTrue(file_exists($this->directory.'/entry-cant-go-here.md'));
    }

    /** @test */
    function it_makes_entry_instances_from_cache()
    {
        $this->markTestIncomplete();
        // It should use Statamic\API\Entry::create()
    }

    /** @test */
    function it_makes_entry_instances_from_files()
    {
        $this->markTestIncomplete();
        // It should use Statamic\API\Entry::create()
    }

    /** @test */
    function it_uses_the_id_of_the_entry_object_combined_with_collection_name_as_the_item_key()
    {
        $entry = Mockery::mock();
        $entry->shouldReceive('id')->andReturn('test');
        $entry->shouldReceive('collectionName')->andReturn('example');

        $this->assertEquals(
            'example::test',
            $this->store->getItemKey($entry, '/path/to/doesnt/matter.yaml')
        );
    }
}
