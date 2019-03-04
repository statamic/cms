<?php

namespace Tests\Stache\Repositories;

use Tests\TestCase;
use Statamic\Stache\Stache;
use Statamic\Data\Taxonomies\Taxonomy;
use Statamic\Stache\Stores\TaxonomiesStore;
use Statamic\API\Taxonomy as TaxonomyAPI;
use Statamic\Stache\Repositories\TaxonomyRepository;
use Illuminate\Support\Collection as IlluminateCollection;

class TaxonomyRepositoryTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $stache = (new Stache)->sites(['en', 'fr']);
        $this->directory = __DIR__.'/../__fixtures__/content/taxonomies';
        $stache->registerStore((new TaxonomiesStore($stache, app('files')))->directory($this->directory));

        $this->repo = new TaxonomyRepository($stache);
    }

    /** @test */
    function it_gets_all_taxonomies()
    {
        $taxonomies = $this->repo->all();

        $this->assertInstanceOf(IlluminateCollection::class, $taxonomies);
        $this->assertCount(2, $taxonomies);
        $this->assertEveryItemIsInstanceOf(Taxonomy::class, $taxonomies);

        $ordered = $taxonomies->sortBy->path()->values();
        $this->assertEquals(['categories', 'tags'], $ordered->map->path()->all()); // TODO: Support ->handle() or ->id()
        $this->assertEquals(['Categories', 'Tags'], $ordered->map->title()->all());
    }

    /** @test */
    function it_gets_a_taxonomy_by_handle()
    {
        tap($this->repo->findByHandle('categories'), function ($taxonomy) {
            $this->assertInstanceOf(Taxonomy::class, $taxonomy);
            $this->assertEquals('categories', $taxonomy->path());
            $this->assertEquals('Categories', $taxonomy->title());
        });

        tap($this->repo->findByHandle('tags'), function ($taxonomy) {
            $this->assertInstanceOf(Taxonomy::class, $taxonomy);
            $this->assertEquals('tags', $taxonomy->path());
            $this->assertEquals('Tags', $taxonomy->title());
        });

        $this->assertNull($this->repo->findByHandle('unknown'));
    }

    /** @test */
    function it_saves_a_taxonomy_to_the_stache_and_to_a_file()
    {
        $taxonomy = TaxonomyAPI::create('new');
        $taxonomy->data(['foo' => 'bar']);
        $this->assertNull($this->repo->findByHandle('new'));

        $this->repo->save($taxonomy);

        $this->assertNotNull($item = $this->repo->findByHandle('new'));
        $this->assertEquals(['foo' => 'bar'], $item->data());
        $this->assertTrue(file_exists($this->directory.'/new.yaml'));
        @unlink($this->directory.'/new.yaml');
    }
}
