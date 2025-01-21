<?php

namespace Tests\Stache\Repositories;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Structures\Tree;
use Statamic\Stache\Repositories\NavTreeRepository;
use Statamic\Stache\Stache;
use Statamic\Stache\Stores\NavTreeStore;
use Tests\TestCase;

class NavTreeRepositoryTest extends TestCase
{
    private $store;
    private $repo;

    public function setUp(): void
    {
        parent::setUp();

        $stache = (new Stache)->sites(['en', 'fr']);
        $this->app->instance(Stache::class, $stache);
        $this->store = $this->mock(NavTreeStore::class)
            ->shouldReceive('key')->andReturn('nav-trees')
            ->getMock();
        $stache->registerStores([$this->store]);
        $this->repo = new NavTreeRepository($stache);
    }

    #[Test]
    public function it_gets_a_nav_tree()
    {
        $this->store
            ->shouldReceive('getItem')
            ->with('links::en')
            ->andReturn($tree = $this->mock(Tree::class));

        $this->assertSame($tree, $this->repo->find('links', 'en'));
    }

    #[Test]
    public function it_saves_a_nav_tree_through_the_store()
    {
        $tree = $this->mock(Tree::class);
        $this->store->shouldReceive('save')->with($tree)->once();

        $this->assertTrue($this->repo->save($tree));
    }
}
