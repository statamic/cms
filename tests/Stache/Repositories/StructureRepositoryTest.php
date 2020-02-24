<?php

namespace Tests\Stache\Repositories;

use Tests\TestCase;
use Statamic\Stache\Stache;
use Illuminate\Support\Collection;
use Statamic\Stache\Stores\EntriesStore;
use Statamic\Stache\Stores\StructuresStore;
use Statamic\Stache\Stores\CollectionsStore;
use Statamic\Contracts\Structures\Structure;
use Statamic\Stache\Repositories\StructureRepository;

class StuctureRepositoryTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $stache = (new Stache)->sites(['en']);
        $this->directory = __DIR__.'/../__fixtures__/content/structures';
        $stache->registerStores([
            (new CollectionsStore($stache, app('files')))->directory(__DIR__.'/../__fixtures__/content/collections'),
            (new EntriesStore($stache, app('files')))->directory(__DIR__.'/../__fixtures__/content/collections'),
            (new StructuresStore($stache, app('files')))->directory($this->directory)
        ]);
        $this->app->instance(Stache::class, $stache);

        $this->repo = new StructureRepository($stache);
    }

    /** @test */
    function it_gets_all_structures()
    {
        $structures = $this->repo->all();

        $this->assertInstanceOf(Collection::class, $structures);
        $this->assertCount(2, $structures);
        $this->assertEveryItemIsInstanceOf(Structure::class, $structures);

        $ordered = $structures->sortBy->handle()->values();
        $this->assertEquals(['collection::pages', 'footer'], $ordered->map->handle()->all());
        $this->assertEquals(['Pages', 'Footer'], $ordered->map->title()->all());
    }

    /** @test */
    function it_gets_a_structure_by_handle()
    {
        tap($this->repo->findByHandle('collection::pages'), function ($structure) {
            $this->assertInstanceOf(Structure::class, $structure);
            $this->assertEquals('collection::pages', $structure->handle());
            $this->assertEquals('Pages', $structure->title());
        });

        tap($this->repo->findByHandle('footer'), function ($structure) {
            $this->assertInstanceOf(Structure::class, $structure);
            $this->assertEquals('footer', $structure->handle());
            $this->assertEquals('Footer', $structure->title());
        });

        $this->assertNull($this->repo->findByHandle('unknown'));
    }

    /** @test */
    function it_saves_a_structure_to_the_stache_and_to_a_file()
    {
        $structure = (new \Statamic\Structures\Structure)->handle('new');
        $structure->addTree($structure->makeTree('en'));

        $this->assertNull($this->repo->findByHandle('new'));

        $this->repo->save($structure);

        $this->assertNotNull($this->repo->findByHandle('new'));
        $this->assertFileExists($this->directory.'/new.yaml');
        @unlink($this->directory.'/new.yaml');
    }

    /** @test */
    function it_gets_an_entry_by_uri()
    {
        $entry = $this->repo->findEntryByUri('/about/board/directors');
        $this->assertEquals('Directors', $entry->title());
        $this->assertEquals('/about/board/directors', $entry->uri());
        $this->assertNull($this->repo->findEntryByUri('/unknown'));
    }
}
