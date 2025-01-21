<?php

namespace Tests\Stache\Repositories;

use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Structures\Structure;
use Statamic\Exceptions\NavigationNotFoundException;
use Statamic\Stache\Repositories\NavigationRepository;
use Statamic\Stache\Stache;
use Statamic\Stache\Stores\CollectionsStore;
use Statamic\Stache\Stores\EntriesStore;
use Statamic\Stache\Stores\NavigationStore;
use Tests\TestCase;

class NavigationRepositoryTest extends TestCase
{
    private $directory;
    private $repo;

    public function setUp(): void
    {
        parent::setUp();

        $stache = (new Stache)->sites(['en']);
        $this->directory = __DIR__.'/../__fixtures__/content/navigation';
        $stache->registerStores([
            (new CollectionsStore($stache, app('files')))->directory(__DIR__.'/../__fixtures__/content/collections'),
            (new EntriesStore($stache, app('files')))->directory(__DIR__.'/../__fixtures__/content/collections'),
            (new NavigationStore($stache, app('files')))->directory($this->directory),
        ]);
        $this->app->instance(Stache::class, $stache);

        $this->repo = new NavigationRepository($stache);
    }

    #[Test]
    public function it_gets_all_navs()
    {
        $navs = $this->repo->all();

        $this->assertInstanceOf(Collection::class, $navs);
        $this->assertCount(2, $navs);
        $this->assertEveryItemIsInstanceOf(Structure::class, $navs);

        $ordered = $navs->sortBy->handle()->values();
        $this->assertEquals(['footer', 'sidebar'], $ordered->map->handle()->all());
        $this->assertEquals(['Footer', 'Sidebar'], $ordered->map->title()->all());
    }

    #[Test]
    public function it_gets_a_nav_by_handle()
    {
        tap($this->repo->findByHandle('sidebar'), function ($nav) {
            $this->assertInstanceOf(Structure::class, $nav);
            $this->assertEquals('sidebar', $nav->handle());
            $this->assertEquals('Sidebar', $nav->title());
        });

        tap($this->repo->findByHandle('footer'), function ($nav) {
            $this->assertInstanceOf(Structure::class, $nav);
            $this->assertEquals('footer', $nav->handle());
            $this->assertEquals('Footer', $nav->title());
        });

        $this->assertNull($this->repo->findByHandle('unknown'));
    }

    #[Test]
    public function it_saves_a_nav_to_the_stache_and_to_a_file()
    {
        $structure = (new \Statamic\Structures\Nav)->handle('new');

        $this->assertNull($this->repo->findByHandle('new'));

        $this->repo->save($structure);

        $this->assertNotNull($this->repo->findByHandle('new'));
        $this->assertFileExists($this->directory.'/new.yaml');
        @unlink($this->directory.'/new.yaml');
    }

    #[Test]
    public function test_find_or_fail_gets_nav()
    {
        $nav = $this->repo->findOrFail('footer');

        $this->assertInstanceOf(Structure::class, $nav);
        $this->assertEquals('Footer', $nav->title());
    }

    #[Test]
    public function test_find_or_fail_throws_exception_when_nav_does_not_exist()
    {
        $this->expectException(NavigationNotFoundException::class);
        $this->expectExceptionMessage('Navigation [does-not-exist] not found');

        $this->repo->findOrFail('does-not-exist');
    }
}
