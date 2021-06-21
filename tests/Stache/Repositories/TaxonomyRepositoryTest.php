<?php

namespace Tests\Stache\Repositories;

use Illuminate\Support\Collection as IlluminateCollection;
use Statamic\Facades\Collection;
use Statamic\Facades\Taxonomy as TaxonomyAPI;
use Statamic\Stache\Repositories\TaxonomyRepository;
use Statamic\Stache\Stache;
use Statamic\Stache\Stores\CollectionsStore;
use Statamic\Stache\Stores\TaxonomiesStore;
use Statamic\Taxonomies\Taxonomy;
use Tests\TestCase;

class TaxonomyRepositoryTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $stache = (new Stache)->sites(['en', 'fr']);
        $this->app->instance(Stache::class, $stache);
        $this->directory = __DIR__.'/../__fixtures__/content/taxonomies';
        $stache->registerStores([
            (new TaxonomiesStore($stache, app('files')))->directory($this->directory),
            (new CollectionsStore($stache, app('files')))->directory(__DIR__.'/../__fixtures__/content/collections'),
        ]);

        $this->repo = new TaxonomyRepository($stache);
    }

    /** @test */
    public function it_gets_all_taxonomies()
    {
        $taxonomies = $this->repo->all();

        $this->assertInstanceOf(IlluminateCollection::class, $taxonomies);
        $this->assertCount(2, $taxonomies);
        $this->assertEveryItemIsInstanceOf(Taxonomy::class, $taxonomies);

        $ordered = $taxonomies->sortBy->handle()->values();
        $this->assertEquals(['categories', 'tags'], $ordered->map->handle()->all());
        $this->assertEquals(['Categories', 'Tags'], $ordered->map->title()->all());
    }

    /** @test */
    public function it_gets_a_taxonomy_by_handle()
    {
        tap($this->repo->findByHandle('categories'), function ($taxonomy) {
            $this->assertInstanceOf(Taxonomy::class, $taxonomy);
            $this->assertEquals('categories', $taxonomy->handle());
            $this->assertEquals('Categories', $taxonomy->title());
        });

        tap($this->repo->findByHandle('tags'), function ($taxonomy) {
            $this->assertInstanceOf(Taxonomy::class, $taxonomy);
            $this->assertEquals('tags', $taxonomy->handle());
            $this->assertEquals('Tags', $taxonomy->title());
        });

        $this->assertNull($this->repo->findByHandle('unknown'));
    }

    /** @test */
    public function it_gets_a_taxonomy_by_uri()
    {
        tap($this->repo->findByUri('/categories'), function ($taxonomy) {
            $this->assertInstanceOf(Taxonomy::class, $taxonomy);
            $this->assertEquals('categories', $taxonomy->handle());
            $this->assertEquals('Categories', $taxonomy->title());
            $this->assertNull($taxonomy->collection());
        });
    }

    /** @test */
    public function it_gets_a_taxonomy_by_uri_with_collection()
    {
        tap($this->repo->findByUri('/blog/categories'), function ($taxonomy) {
            $this->assertInstanceOf(Taxonomy::class, $taxonomy);
            $this->assertEquals('categories', $taxonomy->handle());
            $this->assertEquals('Categories', $taxonomy->title());
            $this->assertEquals(Collection::findByHandle('blog'), $taxonomy->collection());
        });
    }

    /** @test */
    public function it_saves_a_taxonomy_to_the_stache_and_to_a_file()
    {
        $taxonomy = TaxonomyAPI::make('new');
        $taxonomy->cascade(['foo' => 'bar']);
        $this->assertNull($this->repo->findByHandle('new'));

        $this->repo->save($taxonomy);

        $this->assertNotNull($item = $this->repo->findByHandle('new'));
        $this->assertEquals(['foo' => 'bar'], $item->cascade()->all());
        $this->assertTrue(file_exists($this->directory.'/new.yaml'));
        @unlink($this->directory.'/new.yaml');
    }
}
