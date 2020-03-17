<?php

namespace Tests\Data\Structures;

use Statamic\Contracts\Entries\Collection;
use Statamic\Facades\Entry;
use Statamic\Stache\Query\EntryQueryBuilder;
use Statamic\Structures\CollectionStructure;
use Statamic\Structures\Tree;

class CollectionStructureTest extends StructureTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->entryQueryBuilder = $this->mock(EntryQueryBuilder::class);
        $this->entryQueryBuilder->shouldReceive('where')->with('site', 'en')->andReturnSelf();
        $this->entryQueryBuilder->shouldReceive('where')->with('site', 'fr')->andReturnSelf();
        $this->entryQueryBuilder->shouldReceive('get')->andReturnUsing(function () {
            return $this->queryBuilderGetReturnValue();
        });

        $this->collection = $this->mock(Collection::class);
        $this->collection->shouldReceive('queryEntries')->andReturn($this->entryQueryBuilder);
    }

    function structure($handle = null)
    {
        if ($handle !== null) {
            throw new \Exception('Handle should not be set in the test');
        }

        return (new CollectionStructure)->collection($this->collection);
    }

    function queryBuilderGetReturnValue()
    {
        return $this->queryBuilderGetReturnValue ?? collect();
    }

    /** @test */
    function the_handle_comes_from_the_collection()
    {
        $this->collection->shouldReceive('handle')->once()->andReturn('test');

        $this->assertEquals('collection::test', $this->structure()->handle());
    }

    /** @test */
    function the_handle_cannot_be_set()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Handle cannot be set.');

        $this->structure()->handle('test');
    }

    /** @test */
    function the_title_comes_from_the_collection()
    {
        $collection = $this->mock(Collection::class);
        $collection->shouldReceive('title')->once()->andReturn('Test');

        $structure = $this->structure()->collection($collection);

        $this->assertEquals('Test', $structure->collection($collection)->title());
    }

    /** @test */
    function the_title_cannot_be_set()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Title cannot be set.');

        $this->structure()->title('test');
    }

    /** @test */
    function trees_exist_based_on_whether_the_site_is_enabled_on_the_collection()
    {
        $this->collection->shouldReceive('handle')->andReturn('test');
        $this->collection->shouldReceive('sites')->andReturn(collect(['en', 'fr']));

        $structure = $this->structure();
        $structure->addTree($structure->makeTree('en')->tree(['foo' => 'bar']));

        $trees = $structure->trees();
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $trees);
        $this->assertCount(2, $trees);
        $this->assertEveryItemIsInstanceOf(Tree::class, $trees);
        $this->assertTrue($structure->existsIn('en'));
        $this->assertTrue($structure->existsIn('fr'));
        $this->assertFalse($structure->existsIn('de'));
        $this->assertEquals(['foo' => 'bar'], $structure->in('en')->tree()); // manually added tree is maintained
        $this->assertEquals([], $structure->in('fr')->tree());
    }

    /** @test */
    function it_sets_and_gets_the_associated_collection()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    function the_only_available_collection_is_itself()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    function collections_cannot_be_set()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    function it_gets_an_entry_uri()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    function it_gets_the_route_from_the_collection()
    {
        $collection = $this->mock(Collection::class);
        $collection->shouldReceive('route')->once()->andReturn('/the-route/{slug}');

        $this->assertEquals('/the-route/{slug}', $this->structure()->collection($collection)->route('en'));
    }

    /** @test */
    function it_gets_the_route_from_the_collection_when_it_has_multiple()
    {
        $collection = $this->mock(Collection::class);
        $collection->shouldReceive('route')->with('en')->once()->andReturn('/en-route');
        $collection->shouldReceive('route')->with('fr')->once()->andReturn('/fr-route');
        $collection->shouldReceive('route')->with('de')->once()->andReturnNull();

        $structure = $this->structure()->collection($collection);

        $this->assertEquals('/en-route', $structure->route('en'));
        $this->assertEquals('/fr-route', $structure->route('fr'));
        $this->assertNull($structure->route('de'));
    }

    /** @test */
    function entries_may_only_appear_in_the_tree_once()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Duplicate entry [123] in [test] collection\'s structure.');
        $this->collection->shouldReceive('handle')->once()->andReturn('test');

        $this->structure()
            ->validateTree([
                [
                    'entry' => '123',
                ],
                [
                    'entry' => '456',
                    'children' => [
                        [
                            'entry' => '123'
                        ]
                    ]
                ]
            ], 'en');
    }

    /** @test */
    function the_tree_root_can_have_children_when_not_expecting_root()
    {
        $this->queryBuilderGetReturnValue = collect([
            Entry::make()->id('123'),
            Entry::make()->id('456'),
        ]);

        parent::the_tree_root_can_have_children_when_not_expecting_root();
    }

    /** @test */
    function only_entries_belonging_to_the_associated_collection_may_be_in_the_tree()
    {
        $this->queryBuilderGetReturnValue = collect([
            Entry::make()->id('1'),
            Entry::make()->id('2'),
        ]);

        $validated = $this->structure()->validateTree([
            [
                'entry' => '1',
                'children' => [
                    ['entry' => '2'],
                    ['entry' => '4']
                ]
            ],
            ['entry' => '3'],
        ], 'en');

        $this->assertEquals([
            [
                'entry' => '1',
                'children' => [
                    ['entry' => '2']
                ]
            ]
        ], $validated);
    }

    /** @test */
    function entries_not_explicitly_in_the_tree_should_be_appended_to_the_end_of_the_tree()
    {
        $this->queryBuilderGetReturnValue = collect([
            Entry::make()->id('1'),
            Entry::make()->id('2'),
            Entry::make()->id('3'),
            Entry::make()->id('4'),
            Entry::make()->id('5'),
        ]);

        $actual = $this->structure()->validateTree([
            [
                'entry' => '1',
                'children' => [
                    ['entry' => '2']
                ]
            ],
            ['entry' => '3'],
        ], 'en');

        $expected = [
            [
                'entry' => '1',
                'children' => [
                    ['entry' => '2']
                ]
            ],
            ['entry' => '3'],
            ['entry' => '4'],
            ['entry' => '5'],
        ];

        $this->assertEquals($expected, $actual);
    }

    /** @test */
    function it_saves_through_the_collection()
    {
        $structure = $this->structure();
        $collection = $this->mock(Collection::class);
        $collection->shouldReceive('structure')->with($structure)->once()->ordered()->andReturnSelf();
        $collection->shouldReceive('save')->once()->ordered()->andReturnTrue();
        $structure->collection($collection);

        $this->assertTrue($structure->save());
    }
}
