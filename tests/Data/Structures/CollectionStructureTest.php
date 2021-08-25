<?php

namespace Tests\Data\Structures;

use Facades\Statamic\Stache\Repositories\CollectionTreeRepository;
use Statamic\Contracts\Entries\Collection;
use Statamic\Contracts\Entries\Entry as EntryContract;
use Statamic\Facades;
use Statamic\Facades\Blink;
use Statamic\Facades\Entry;
use Statamic\Stache\Query\EntryQueryBuilder;
use Statamic\Structures\CollectionStructure;
use Statamic\Structures\CollectionTree;
use Statamic\Structures\Page;
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

    public function structure($handle = null)
    {
        return (new CollectionStructure)->handle($handle);
    }

    public function queryBuilderGetReturnValue()
    {
        return $this->queryBuilderGetReturnValue ?? collect();
    }

    /** @test */
    public function it_gets_and_sets_the_handle()
    {
        $structure = $this->structure();
        $this->assertNull($structure->handle());

        $return = $structure->handle('test');

        $this->assertEquals('test', $structure->handle());
        $this->assertEquals($structure, $return);
    }

    /** @test */
    public function it_gets_the_collection()
    {
        $structure = $this->structure('test');
        $collection = $this->mock(Collection::class);
        Facades\Collection::shouldReceive('findByHandle')->with('test')->once()->andReturn($collection);

        $this->assertNull(Blink::get($blinkKey = 'collection-structure-collection-test'));

        // Do it twice combined with the once() in the mock to show blink works.
        $this->assertEquals($collection, $structure->collection());
        $this->assertEquals($collection, $structure->collection());
        $this->assertSame($collection, Blink::get($blinkKey));
    }

    /** @test */
    public function it_makes_a_tree()
    {
        Facades\Collection::shouldReceive('findByHandle')->andReturn($this->collection);
        $structure = $this->structure()->handle('test');
        $this->collection->shouldReceive('structure')->andReturn($structure);
        $this->collection->shouldReceive('handle')->andReturn('test');

        $this->queryBuilderGetReturnValue = collect([
            Entry::make()->id('1'),
        ]);

        $tree = $structure->makeTree('fr', [
            ['entry' => 1],
        ]);
        $this->assertEquals('fr', $tree->locale());
        $this->assertEquals('test', $tree->handle());
        $this->assertEquals([
            ['entry' => 1],
        ], $tree->tree());
    }

    /** @test */
    public function the_title_comes_from_the_collection()
    {
        $collection = $this->mock(Collection::class);
        $collection->shouldReceive('title')->once()->andReturn('Test');
        Facades\Collection::shouldReceive('findByHandle')->andReturn($collection);

        $structure = $this->structure();

        $this->assertEquals('Test', $structure->title());
    }

    /** @test */
    public function the_title_cannot_be_set()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Title cannot be set.');

        $this->structure()->title('test');
    }

    /** @test */
    public function trees_exist_based_on_whether_the_site_is_enabled_on_the_collection()
    {
        // ...unlike nav trees, which only exist if there's a tree file.

        $structure = $this->structure('test');

        $this->collection->shouldReceive('handle')->andReturn('test');
        $this->collection->shouldReceive('sites')->andReturn(collect(['en', 'fr']));
        $this->collection->shouldReceive('structure')->andReturn($structure);

        Facades\Collection::shouldReceive('findByHandle')->with('test')->andReturn($this->collection);

        CollectionTreeRepository::shouldReceive('find')->with('test', 'en')->andReturn($enTree = $structure->makeTree('en'));
        CollectionTreeRepository::shouldReceive('find')->with('test', 'fr')->andReturnNull();
        CollectionTreeRepository::shouldReceive('find')->with('test', 'de')->andReturnNull();

        $trees = $structure->trees();
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $trees);
        $this->assertCount(2, $trees);
        $this->assertEveryItemIsInstanceOf(Tree::class, $trees);
        $this->assertTrue($structure->existsIn('en'));
        $this->assertTrue($structure->existsIn('fr'));
        $this->assertFalse($structure->existsIn('de'));
        $this->assertSame($enTree, $structure->in('en'));
        $this->assertInstanceOf(CollectionTree::class, $structure->in('fr'));
        $this->assertNull($structure->in('de'));
    }

    /** @test */
    public function it_sets_and_gets_the_associated_collection()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    public function the_only_available_collection_is_itself()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    public function collections_cannot_be_set()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    public function it_gets_an_entry_uri()
    {
        Facades\Collection::shouldReceive('findByHandle')->with('test')->andReturn($this->collection);

        $structure = $this->structure('test');

        $this->collection->shouldReceive('handle')->andReturn('test');
        $this->collection->shouldReceive('route')->with('en')->once()->andReturn('{slug}');

        $page = $this->mock(Page::class);
        $page->shouldReceive('reference')->andReturn('the-entry-id');
        $page->shouldReceive('uri')->andReturn('/the-uri-from-the-page');

        $tree = $this->mock(Tree::class);
        $tree->shouldReceive('structure')->andReturn($structure);
        $tree->shouldReceive('locale')->andReturn('en');
        $tree->shouldReceive('flattenedPages')->andReturn(collect([$page]));

        CollectionTreeRepository::shouldReceive('find')->with('test', 'en')->andReturn($tree);

        $entry = $this->mock(EntryContract::class);
        $entry->shouldReceive('id')->andReturn('the-entry-id');
        $entry->shouldReceive('locale')->andReturn('en');

        $this->assertEquals('/the-uri-from-the-page', $structure->entryUri($entry));
    }

    /** @test */
    public function the_entry_uri_is_null_if_the_collection_doesnt_have_a_route()
    {
        $structure = $this->structure('test');
        Facades\Collection::shouldReceive('findByHandle')->with('test')->andReturn($this->collection);

        $this->collection->shouldReceive('route')->with('en')->once()->andReturnNull();

        $entry = $this->mock(EntryContract::class);
        $entry->shouldReceive('id')->andReturn('the-entry-id');
        $entry->shouldReceive('locale')->andReturn('en');

        $this->assertNull($structure->entryUri($entry));
    }

    /** @test */
    public function it_gets_the_route_from_the_collection()
    {
        $collection = $this->mock(Collection::class);
        $collection->shouldReceive('route')->once()->andReturn('/the-route/{slug}');
        Facades\Collection::shouldReceive('findByHandle')->with('test')->andReturn($collection);

        $this->assertEquals('/the-route/{slug}', $this->structure('test')->route('en'));
    }

    /** @test */
    public function it_gets_the_route_from_the_collection_when_it_has_multiple()
    {
        $collection = $this->mock(Collection::class);
        $collection->shouldReceive('route')->with('en')->once()->andReturn('/en-route');
        $collection->shouldReceive('route')->with('fr')->once()->andReturn('/fr-route');
        $collection->shouldReceive('route')->with('de')->once()->andReturnNull();

        $structure = $this->structure('test');
        Facades\Collection::shouldReceive('findByHandle')->with('test')->andReturn($collection);

        $this->assertEquals('/en-route', $structure->route('en'));
        $this->assertEquals('/fr-route', $structure->route('fr'));
        $this->assertNull($structure->route('de'));
    }

    /** @test */
    public function entries_may_only_appear_in_the_tree_once()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Duplicate entry [123] in [test] collection\'s structure.');
        $this->collection->shouldReceive('handle')->once()->andReturn('test');
        Facades\Collection::shouldReceive('findByHandle')->with('test')->andReturn($this->collection);

        $this->structure('test')
            ->validateTree([
                [
                    'entry' => '123',
                ],
                [
                    'entry' => '456',
                    'children' => [
                        [
                            'entry' => '123',
                        ],
                    ],
                ],
            ], 'en');
    }

    /** @test */
    public function the_tree_root_can_have_children_when_not_expecting_root()
    {
        Facades\Collection::shouldReceive('findByHandle')->with('test')->andReturn($this->collection);

        $this->queryBuilderGetReturnValue = collect([
            Entry::make()->id('123'),
            Entry::make()->id('456'),
        ]);

        parent::the_tree_root_can_have_children_when_not_expecting_root();
    }

    /** @test */
    public function only_entries_belonging_to_the_associated_collection_may_be_in_the_tree()
    {
        Facades\Collection::shouldReceive('findByHandle')->with('test')->andReturn($this->collection);

        $this->queryBuilderGetReturnValue = collect([
            Entry::make()->id('1'),
            Entry::make()->id('2'),
        ]);

        $validated = $this->structure('test')->validateTree([
            [
                'entry' => '1',
                'children' => [
                    ['entry' => '2'],
                    ['entry' => '4'],
                ],
            ],
            ['entry' => '3'],
        ], 'en');

        $this->assertEquals([
            [
                'entry' => '1',
                'children' => [
                    ['entry' => '2'],
                ],
            ],
        ], $validated);
    }

    /** @test */
    public function entries_not_explicitly_in_the_tree_should_be_appended_to_the_end_of_the_tree()
    {
        Facades\Collection::shouldReceive('findByHandle')->with('test')->andReturn($this->collection);

        $this->queryBuilderGetReturnValue = collect([
            Entry::make()->id('1'),
            Entry::make()->id('2'),
            Entry::make()->id('3'),
            Entry::make()->id('4'),
            Entry::make()->id('5'),
        ]);

        $actual = $this->structure('test')->validateTree([
            [
                'entry' => '1',
                'children' => [
                    ['entry' => '2'],
                ],
            ],
            ['entry' => '3'],
        ], 'en');

        $expected = [
            [
                'entry' => '1',
                'children' => [
                    ['entry' => '2'],
                ],
            ],
            ['entry' => '3'],
            ['entry' => '4'],
            ['entry' => '5'],
        ];

        $this->assertEquals($expected, $actual);
    }

    /** @test */
    public function it_saves_through_the_collection()
    {
        $structure = $this->structure('test');
        $collection = $this->mock(Collection::class);
        $collection->shouldReceive('structure')->with($structure)->once()->ordered()->andReturnSelf();
        $collection->shouldReceive('save')->once()->ordered()->andReturnTrue();
        Facades\Collection::shouldReceive('findByHandle')->with('test')->andReturn($collection);

        $this->assertTrue($structure->save());
    }
}
