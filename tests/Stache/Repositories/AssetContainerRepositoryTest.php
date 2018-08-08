<?php

namespace Tests\Stache\Repositories;

use Tests\TestCase;
use Statamic\Stache\Stache;
use Statamic\Contracts\Assets\AssetContainer;
use Statamic\Stache\Stores\AssetContainersStore;
use Illuminate\Support\Collection as IlluminateCollection;
use Statamic\Stache\Repositories\AssetContainerRepository;

class AssetContainerRepositoryTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $stache = (new Stache)->sites(['en', 'fr']);
        $stache->registerStore((new AssetContainersStore($stache))->directory(__DIR__.'/../__fixtures__/content/assets'));

        $this->repo = new AssetContainerRepository($stache);
    }

    /** @test */
    function it_gets_all_asset_containers()
    {
        $containers = $this->repo->all();

        $this->assertInstanceOf(IlluminateCollection::class, $containers);
        $this->assertCount(2, $containers);
        $this->assertEveryItemIsInstanceOf(AssetContainer::class, $containers);

        $ordered = $containers->sortBy->handle()->values();
        $this->assertEquals(['another', 'main'], $ordered->map->id()->all());
        $this->assertEquals(['another', 'main'], $ordered->map->handle()->all());
        $this->assertEquals(['Another Asset Container', 'Main Assets'], $ordered->map->title()->all());
    }

    /** @test */
    function it_gets_an_asset_container_by_handle()
    {
        tap($this->repo->findByHandle('main'), function ($container) {
            $this->assertInstanceOf(AssetContainer::class, $container);
            $this->assertEquals('main', $container->id());
            $this->assertEquals('main', $container->handle());
            $this->assertEquals('Main Assets', $container->title());
        });

        tap($this->repo->findByHandle('another'), function ($container) {
            $this->assertInstanceOf(AssetContainer::class, $container);
            $this->assertEquals('another', $container->id());
            $this->assertEquals('another', $container->handle());
            $this->assertEquals('Another Asset Container', $container->title());
        });

        $this->assertNull($this->repo->findByHandle('unknown'));
    }
}
