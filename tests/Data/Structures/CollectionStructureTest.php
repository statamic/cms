<?php

namespace Tests\Data\Structures;

use Statamic\Contracts\Entries\Collection;
use Statamic\Structures\CollectionStructure;

class CollectionStructureTest extends StructureTestCase
{
    function structure($handle = null)
    {
        if ($handle !== null) {
            throw new \Exception('Handle should not be set in the test');
        }

        return new CollectionStructure;
    }

    /** @test */
    function the_handle_comes_from_the_collection()
    {
        $collection = $this->mock(Collection::class);
        $collection->shouldReceive('handle')->once()->andReturn('test');

        $structure = $this->structure()->collection($collection);

        $this->assertEquals('collection::test', $structure->collection($collection)->handle());
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
        $collection->shouldReceive('route')->times(3)->andReturn([
            'en' => '/en-route',
            'fr' => '/fr-route',
        ]);

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

        $collection = $this->mock(Collection::class);
        $collection->shouldReceive('handle')->once()->andReturn('test');

        $this->structure()
            ->collection($collection)
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
            ]);
    }
}
