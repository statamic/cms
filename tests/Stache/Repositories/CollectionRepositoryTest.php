<?php

namespace Tests\Stache\Repositories;

use Tests\TestCase;
use Statamic\Stache\Stache;
use Statamic\Data\Entries\Collection;
use Statamic\Stache\Stores\CollectionsStore;
use Statamic\API\Collection as CollectionAPI;
use Statamic\Stache\Repositories\CollectionRepository;
use Illuminate\Support\Collection as IlluminateCollection;

class CollectionRepositoryTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $stache = (new Stache)->sites(['en', 'fr']);
        $this->directory = __DIR__.'/../__fixtures__/content/collections';
        $stache->registerStore((new CollectionsStore($stache, app('files')))->directory($this->directory));

        $this->repo = new CollectionRepository($stache);
    }

    /** @test */
    function it_gets_all_collections()
    {
        $collections = $this->repo->all();

        $this->assertInstanceOf(IlluminateCollection::class, $collections);
        $this->assertCount(4, $collections);
        $this->assertEveryItemIsInstanceOf(Collection::class, $collections);

        $ordered = $collections->sortBy->path()->values();
        $this->assertEquals(['alphabetical', 'blog', 'numeric', 'pages'], $ordered->map->path()->all()); // @TODO: Support ->handle() or ->id()
        $this->assertEquals(['Alphabetical', 'Blog', 'Numeric', 'Pages'], $ordered->map->title()->all());
        $this->assertEquals(['alphabetical', 'date', 'number', 'alphabetical'], $ordered->map->order()->all());
    }

    /** @test */
    function it_gets_a_collection_by_handle()
    {
        tap($this->repo->findByHandle('alphabetical'), function ($collection) {
            $this->assertInstanceOf(Collection::class, $collection);
            $this->assertEquals('alphabetical', $collection->path());
            $this->assertEquals('Alphabetical', $collection->title());
            $this->assertEquals('alphabetical', $collection->order());
        });

        tap($this->repo->findByHandle('blog'), function ($collection) {
            $this->assertInstanceOf(Collection::class, $collection);
            $this->assertEquals('blog', $collection->path());
            $this->assertEquals('Blog', $collection->title());
            $this->assertEquals('date', $collection->order());
        });

        tap($this->repo->findByHandle('numeric'), function ($collection) {
            $this->assertInstanceOf(Collection::class, $collection);
            $this->assertEquals('numeric', $collection->path());
            $this->assertEquals('Numeric', $collection->title());
            $this->assertEquals('number', $collection->order());
        });

        tap($this->repo->findByHandle('pages'), function ($collection) {
            $this->assertInstanceOf(Collection::class, $collection);
            $this->assertEquals('pages', $collection->path());
            $this->assertEquals('Pages', $collection->title());
            $this->assertEquals('alphabetical', $collection->order());
        });

        $this->assertNull($this->repo->findByHandle('unknown'));
    }

    /** @test */
    function it_saves_a_collection_to_the_stache_and_to_a_file()
    {
        $collection = CollectionAPI::create('new');
        $collection->data(['foo' => 'bar']);
        $this->assertNull($this->repo->findByHandle('new'));

        $this->repo->save($collection);

        $this->assertNotNull($item = $this->repo->findByHandle('new'));
        $this->assertEquals(['foo' => 'bar'], $item->data());
        $this->assertTrue(file_exists($this->directory.'/new.yaml'));
        @unlink($this->directory.'/new.yaml');
    }
}
