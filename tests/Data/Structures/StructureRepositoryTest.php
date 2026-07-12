<?php

namespace Tests\Data\Structures;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Structures\Structure as StructureContract;
use Statamic\Facades\Collection;
use Statamic\Facades\Nav;
use Statamic\Structures\CollectionStructure;
use Statamic\Structures\StructureRepository;
use Tests\TestCase;

class StructureRepositoryTest extends TestCase
{
    private $repo;

    public function setUp(): void
    {
        parent::setUp();

        $this->repo = new StructureRepository;
    }

    #[Test]
    public function it_gets_all_structures()
    {
        $navs = collect([
            Nav::make('nav-a'),
            Nav::make('nav-b'),
            Nav::make('nav-c'),
        ]);
        Nav::shouldReceive('all')->andReturn($navs);

        $collections = collect([
            Collection::make('collection-structure-a')->structure(new CollectionStructure),
            Collection::make('collection-structure-b')->structure(new CollectionStructure),
        ]);
        Collection::shouldReceive('whereStructured')->andReturn($collections);

        $structures = $this->repo->all();

        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $structures);
        $this->assertCount(5, $structures);
        $this->assertEveryItemIsInstanceOf(StructureContract::class, $structures);
        $this->assertEquals([0, 1, 2, 3, 4], $structures->keys()->all());
        $this->assertEquals([
            'nav-a',
            'nav-b',
            'nav-c',
            'collection-structure-a',
            'collection-structure-b',
        ], $structures->map->handle()->all());
    }

    #[Test]
    public function it_gets_a_nav_structure_by_handle()
    {
        $nav = Nav::make();
        Nav::shouldReceive('find')->with('test')->once()->andReturn($nav);
        Collection::shouldReceive('find')->never();

        $this->assertSame($nav, $this->repo->find('test'));
    }

    #[Test]
    public function it_gets_a_collection_structure_by_handle()
    {
        $structure = new CollectionStructure;
        $collection = Collection::make()->structure($structure);
        Collection::shouldReceive('find')->with('test')->once()->andReturn($collection);
        Nav::shouldReceive('find')->never();

        $this->assertSame($structure, $this->repo->find('collection::test'));
    }

    #[Test]
    public function it_gets_an_entry_by_uri()
    {
        $this->markTestIncomplete();
    }
}
