<?php

namespace Tests\Stache\Repositories;

use Statamic\Contracts\Entries\Collection;
use Statamic\Contracts\Structures\Tree;
use Statamic\Stache\Repositories\CollectionTreeRepository;
use Statamic\Stache\Stache;
use Statamic\Stache\Stores\CollectionTreeStore;
use Tests\TestCase;

class CollectionTreeRepositoryTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $stache = (new Stache)->sites(['en', 'fr']);
        $this->app->instance(Stache::class, $stache);
        $this->store = $this->mock(CollectionTreeStore::class)
            ->shouldReceive('key')->andReturn('collection-trees')
            ->getMock();
        $stache->registerStores([$this->store]);
        $this->repo = new CollectionTreeRepository($stache);
    }

    /** @test */
    public function it_gets_a_collection_tree()
    {
        $this->store
            ->shouldReceive('getItem')
            ->with('pages::en')
            ->andReturn($tree = $this->mock(Tree::class));

        $this->assertSame($tree, $this->repo->find('pages', 'en'));
    }

    /** @test */
    public function it_saves_a_nav_tree_through_the_store()
    {
        $tree = $this->mock(Tree::class);

        $collection = $this->mock(Collection::class);
        $collection->shouldReceive('orderable')->andReturnFalse();
        $tree->shouldReceive('collection')->andReturn($collection);

        $this->store->shouldReceive('save')->with($tree)->once();

        $this->assertTrue($this->repo->save($tree));
    }

    /** @test */
    public function it_updates_the_order_index_for_entries_when_saving()
    {
        $this->markTestIncomplete();
    }
}
