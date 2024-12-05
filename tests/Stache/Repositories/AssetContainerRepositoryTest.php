<?php

namespace Tests\Stache\Repositories;

use Illuminate\Support\Collection as IlluminateCollection;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Assets\AssetContainer;
use Statamic\Exceptions\AssetContainerNotFoundException;
use Statamic\Facades;
use Statamic\Stache\Repositories\AssetContainerRepository;
use Statamic\Stache\Stache;
use Statamic\Stache\Stores\AssetContainersStore;
use Tests\TestCase;

class AssetContainerRepositoryTest extends TestCase
{
    private $directory;
    private $repo;

    public function setUp(): void
    {
        parent::setUp();

        $stache = (new Stache)->sites(['en', 'fr']);
        $this->app->instance(Stache::class, $stache);
        $this->directory = __DIR__.'/../__fixtures__/content/assets';
        $stache->registerStore((new AssetContainersStore($stache, app('files')))->directory($this->directory));

        $this->repo = new AssetContainerRepository($stache);
    }

    #[Test]
    public function it_gets_all_asset_containers()
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

    #[Test]
    public function it_gets_an_asset_container_by_handle()
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

    #[Test]
    public function it_saves_a_container_to_the_stache_and_to_a_file()
    {
        $container = Facades\AssetContainer::make('new');
        $this->assertNull($this->repo->findByHandle('new'));

        $this->repo->save($container);

        $this->assertNotNull($item = $this->repo->findByHandle('new'));
        $this->assertEquals($container, $item);
        $this->assertTrue(file_exists($this->directory.'/new.yaml'));
        @unlink($this->directory.'/new.yaml');
    }

    #[Test]
    public function test_find_or_fail_gets_container()
    {
        $container = $this->repo->findOrFail('main');

        $this->assertInstanceOf(AssetContainer::class, $container);
        $this->assertEquals('Main Assets', $container->title());
    }

    #[Test]
    public function test_find_or_fail_throws_exception_when_container_does_not_exist()
    {
        $this->expectException(AssetContainerNotFoundException::class);
        $this->expectExceptionMessage('Asset Container [does-not-exist] not found');

        $this->repo->findOrFail('does-not-exist');
    }
}
