<?php

namespace Tests\Stache\Stores;

use Mockery;
use Tests\TestCase;
use Statamic\Stache\Stache;
use Facades\Statamic\Stache\Traverser;
use Statamic\Stache\Stores\EntriesStore;
use Statamic\Contracts\Data\Entries\Entry;

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
        $cache = collect([
            '1' => [
                'attributes' => [
                    'slug' => 'test',
                    'collection' => 'blog',
                    'order' => '1',
                    'published' => true,
                    'data_type' => 'md',
                ],
                'data' => [
                    'en' => [
                        'id' => '1',
                        'title' => 'Test Entry',
                    ],
                    'fr' => [
                        'id' => '1',
                        'title' => 'Le Test Entry',
                    ]
                ]
            ]
        ]);

        $items = $this->store->getItemsFromCache($cache);

        $this->assertCount(1, $items);
        tap($items->first(), function ($entry) {
            $this->assertInstanceOf(Entry::class, $entry);
            $this->assertEquals('1', $entry->id());
            $this->assertEquals('Test Entry', $entry->get('title'));
            $this->assertEquals('Le Test Entry', $entry->in('fr')->get('title'));
            $this->assertEquals('test', $entry->slug());
            $this->assertEquals('1', $entry->order());
            $this->assertTrue($entry->published());
        });
    }

    /** @test */
    function it_makes_entry_instances_from_files()
    {
        $item = $this->store->createItemFromFile(
            $this->directory.'/blog/2017-01-02.my-post.md',
            "id: 123\ntitle: Example\nfoo: bar"
        );

        $this->assertInstanceOf(Entry::class, $item);
        $this->assertEquals('123', $item->id());
        $this->assertEquals('Example', $item->get('title'));
        $this->assertEquals(['id' => '123', 'title' => 'Example', 'foo' => 'bar'], $item->data());
        $this->assertEquals('2017-01-02', $item->order());
        $this->assertEquals('my-post', $item->slug());
        $this->assertTrue($item->published());
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
