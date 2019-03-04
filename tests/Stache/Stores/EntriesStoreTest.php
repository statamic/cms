<?php

namespace Tests\Stache\Stores;

use Mockery;
use Statamic\API;
use Tests\TestCase;
use Statamic\Stache\Stache;
use Facades\Statamic\Stache\Traverser;
use Statamic\Stache\Stores\EntriesStore;
use Statamic\Contracts\Data\Entries\Entry;

class EntriesStoreTest extends TestCase
{
    function setUp(): void
    {
        parent::setUp();

        $stache = (new Stache)->sites(['en']);
        $this->store = (new EntriesStore($stache, app('files')))
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
        API\Collection::shouldReceive('whereHandle')->with('blog')->andReturn(new \Statamic\Data\Entries\Collection);

        $cache = collect([
            '123' => [
                'collection' => 'blog',
                'localizations' => [
                    'en' => [
                        'slug' => 'test',
                        'order' => '1',
                        'published' => true,
                        'path' => '/path/to/en.md',
                        'data' => [
                            'title' => 'Test Entry',
                        ]
                    ],
                    'fr' => [
                        'order' => '3',
                        'published' => false,
                        'slug' => 'le-test',
                        'path' => '/path/to/fr.md',
                        'data' => [
                            'title' => 'Le Test Entry',
                        ]
                    ]
                ]
            ]
        ]);

        $items = $this->store->getItemsFromCache($cache);

        $this->assertCount(1, $items);
        $entry = $items->first();
        $this->assertInstanceOf(Entry::class, $entry);
        $this->assertCount(2, $entry->localizations());
        $this->assertEquals('123', $entry->id());
        tap($entry->in('en'), function ($entry) {
            $this->assertEquals('test', $entry->slug());
            $this->assertEquals('1', $entry->order());
            $this->assertEquals('/path/to/en.md', $entry->initialPath());
            $this->assertTrue($entry->published());
            $this->assertEquals('Test Entry', $entry->get('title'));
        });
        tap($entry->in('fr'), function ($entry) {
            $this->assertEquals('le-test', $entry->slug());
            $this->assertEquals('3', $entry->order());
            $this->assertEquals('/path/to/fr.md', $entry->initialPath());
            $this->assertFalse($entry->published());
            $this->assertEquals('Le Test Entry', $entry->get('title'));
        });
    }

    /** @test */
    function it_makes_entry_instances_from_files()
    {
        API\Collection::shouldReceive('whereHandle')->with('blog')->andReturn(
            new \Statamic\Data\Entries\Collection
        );

        $item = $this->store->createItemFromFile(
            $this->directory.'/blog/2017-01-02.my-post.md',
            "id: 123\ntitle: Example\nfoo: bar"
        );

        $this->assertInstanceOf(Entry::class, $item);
        $this->assertEquals('123', $item->id());
        $this->assertEquals('Example', $item->get('title'));
        $this->assertEquals(['title' => 'Example', 'foo' => 'bar'], $item->data());
        $this->assertEquals('2017-01-02', $item->order());
        $this->assertEquals('my-post', $item->slug());
        $this->assertTrue($item->published());
    }

    /** @test */
    function it_uses_the_id_of_the_entry_object_combined_with_collection_handle_as_the_item_key()
    {
        $entry = Mockery::mock();
        $entry->shouldReceive('id')->andReturn('test');
        $entry->shouldReceive('collectionHandle')->andReturn('example');

        $this->assertEquals(
            'example::test',
            $this->store->getItemKey($entry, '/path/to/doesnt/matter.yaml')
        );
    }

    /** @test */
    function it_saves_to_disk()
    {
        API\Stache::shouldReceive('store')->with('entries')->andReturn($this->store);

        $entry = (new \Statamic\Data\Entries\Entry)
            ->id('test-blog-entry')
            ->collection((new \Statamic\Data\Entries\Collection)->handle('blog'))
            ->in('en', function ($loc) {
                $loc
                    ->slug('test')
                    ->order('2017-07-04')
                    ->data(['foo' => 'bar', 'content' => 'test content']);
            });

        $this->store->save($entry);

        $contents = <<<EOT
---
foo: bar
id: test-blog-entry
---
test content
EOT;
        $this->assertFileEqualsString($path = $this->directory.'/blog/2017-07-04.test.md', $contents);
        @unlink($path);
        $this->assertFileNotExists($path);
    }
}
