<?php

namespace Tests\Data\Structures;

use Statamic\Contracts\Structures\Structure as StructureContract;
use Statamic\Facades\Collection;
use Statamic\Facades\Nav;
use Statamic\Structures\Structure;
use Statamic\Structures\StructureRepository;
use Tests\TestCase;

class StuctureRepositoryTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->repo = new StructureRepository;
    }

    /** @test */
    function it_gets_all_structures()
    {
        Nav::shouldReceive('all')->andReturn(collect([
            (new Structure)->handle('nav-a'),
            (new Structure)->handle('nav-b'),
            (new Structure)->handle('nav-c'),
        ]));

        $collections = collect([
            Collection::make('collection-structure-a')->structure(new Structure),
            Collection::make('collection-structure-b')->structure(new Structure),
        ]);
        Collection::shouldReceive('whereStructured')->andReturn($collections);

        $structures = $this->repo->all();

        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $structures);
        $this->assertCount(5, $structures);
        $this->assertEveryItemIsInstanceOf(StructureContract::class, $structures);
        $this->assertEquals([
            'nav-a',
            'nav-b',
            'nav-c',
            'collection::collection-structure-a',
            'collection::collection-structure-b',
        ], $structures->map->handle()->all());
    }

    /** @test */
    function it_gets_a_nav_structure_by_handle()
    {
        Nav::shouldReceive('find')->with('test')->once()->andReturn($structure = new Structure);
        Collection::shouldReceive('find')->never();

        $this->assertSame($structure, $this->repo->find('test'));
    }

    /** @test */
    function it_gets_a_collection_structure_by_handle()
    {
        $structure = new Structure;
        $collection = Collection::make()->structure($structure);
        Collection::shouldReceive('find')->with('test')->once()->andReturn($collection);
        Nav::shouldReceive('find')->never();

        $this->assertSame($structure, $this->repo->find('collection::test'));
    }

    /** @test */
    function it_gets_an_entry_by_uri()
    {
        $this->markTestIncomplete();
    }
}
