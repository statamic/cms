<?php

namespace Tests\Data\Entries;

use Facades\Statamic\Fields\BlueprintRepository;
use Facades\Tests\Factories\EntryFactory;
use Statamic\Entries\Collection;
use Statamic\Entries\Entry;
use Statamic\Facades;
use Statamic\Facades\Site;
use Statamic\Facades\Structure;
use Statamic\Fields\Blueprint;
use Statamic\Structures\CollectionStructure;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class CollectionTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
    function it_gets_and_sets_the_handle()
    {
        $collection = new Collection;
        $this->assertNull($collection->handle());

        $return = $collection->handle('foo');

        $this->assertEquals($collection, $return);
        $this->assertEquals('foo', $collection->handle());
    }

    /** @test */
    function it_gets_and_sets_the_routes()
    {
        Site::setConfig(['sites' => [
            'en' => ['url' => 'http://domain.com/'],
            'fr' => ['url' => 'http://domain.com/fr/'],
            'de' => ['url' => 'http://domain.com/de/'],
        ]]);

        // A collection with no sites uses the default site.
        $collection = new Collection;
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $collection->routes());
        $this->assertEquals(['en' => null], $collection->routes()->all());

        $return = $collection->routes([
            'en' => 'blog/{slug}',
            'fr' => 'le-blog/{slug}',
            'de' => 'das-blog/{slug}'
        ]);

        $this->assertEquals($collection, $return);
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $collection->routes());

        // Only routes corresponding to the collection's sites will be returned.
        $this->assertEquals(['en' => 'blog/{slug}'], $collection->routes()->all());
        $this->assertEquals('blog/{slug}', $collection->route('en'));
        $this->assertNull($collection->route('fr'));
        $this->assertNull($collection->route('de'));
        $this->assertNull($collection->route('unknown'));

        $collection->sites(['en', 'fr']);

        $this->assertEquals([
            'en' => 'blog/{slug}',
            'fr' => 'le-blog/{slug}'
        ], $collection->routes()->all());
        $this->assertEquals('blog/{slug}', $collection->route('en'));
        $this->assertEquals('le-blog/{slug}', $collection->route('fr'));
        $this->assertNull($collection->route('de'));
        $this->assertNull($collection->route('unknown'));
    }

    /** @test */
    function it_sets_all_the_routes_identically()
    {
        Site::setConfig(['sites' => [
            'en' => ['url' => 'http://domain.com/'],
            'fr' => ['url' => 'http://domain.com/fr/'],
            'de' => ['url' => 'http://domain.com/de/'],
        ]]);

        $collection = (new Collection)->sites(['en', 'fr']);

        $return = $collection->routes('{slug}');

        $this->assertEquals($collection, $return);
        $this->assertEquals([
            'en' => '{slug}',
            'fr' => '{slug}'
        ], $collection->routes()->all());
        $this->assertEquals('{slug}', $collection->route('en'));
        $this->assertEquals('{slug}', $collection->route('fr'));
        $this->assertNull($collection->route('de'));
        $this->assertNull($collection->route('unknown'));
    }

    /** @test */
    function it_gets_and_sets_the_template()
    {
        $collection = new Collection;
        $this->assertEquals('default', $collection->template());

        $return = $collection->template('foo');

        $this->assertEquals($collection, $return);
        $this->assertEquals('foo', $collection->template());
    }

    /** @test */
    function it_gets_and_sets_the_layout()
    {
        $collection = new Collection;
        $this->assertEquals('layout', $collection->layout());

        $return = $collection->layout('foo');

        $this->assertEquals($collection, $return);
        $this->assertEquals('foo', $collection->layout());
    }

    /** @test */
    function it_gets_and_sets_the_title()
    {
        $collection = (new Collection)->handle('blog');
        $this->assertEquals('Blog', $collection->title());

        $return = $collection->title('The Blog');

        $this->assertEquals($collection, $return);
        $this->assertEquals('The Blog', $collection->title());
    }

    /** @test */
    function it_gets_and_sets_the_sites_it_can_be_used_in_when_using_multiple_sites()
    {
        Site::setConfig(['sites' => [
            'en' => ['url' => 'http://domain.com/'],
            'fr' => ['url' => 'http://domain.com/fr/'],
        ]]);

        $collection = new Collection;

        $sites = $collection->sites();
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $sites);
        $this->assertEquals(['en'], $sites->all()); // collection with no sites will resolve to the default site.

        $return = $collection->sites(['en', 'fr']);

        $this->assertEquals($collection, $return);
        $this->assertEquals(['en', 'fr'], $collection->sites()->all());
    }

    /** @test */
    function it_gets_the_default_site_when_in_single_site_mode()
    {
        $collection = new Collection;

        $sites = $collection->sites();
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $sites);
        $this->assertEquals(['en'], $sites->all());

        $return = $collection->sites(['en', 'fr']); // has no effect

        $this->assertEquals($collection, $return);
        $this->assertEquals(['en'], $collection->sites()->all());
    }

    /** @test */
    function it_stores_cascading_data_in_a_collection()
    {
        $collection = new Collection;
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $collection->cascade());
        $this->assertTrue($collection->cascade()->isEmpty());

        $collection->cascade()->put('foo', 'bar');

        $this->assertTrue($collection->cascade()->has('foo'));
        $this->assertEquals('bar', $collection->cascade()->get('foo'));
    }

    /** @test */
    function it_sets_all_the_cascade_data_when_passing_an_array()
    {
        $collection = new Collection;

        $return = $collection->cascade($arr = ['foo' => 'bar', 'baz' => 'qux']);
        $this->assertEquals($collection, $return);
        $this->assertEquals($arr, $collection->cascade()->all());

        // test that passing an empty array is not treated as passing null
        $return = $collection->cascade([]);
        $this->assertEquals($collection, $return);
        $this->assertEquals([], $collection->cascade()->all());
    }

    /** @test */
    function it_gets_values_from_the_cascade_with_fallbacks()
    {
        $collection = new Collection;
        $collection->cascade(['foo' => 'bar']);

        $this->assertEquals('bar', $collection->cascade('foo'));
        $this->assertNull($collection->cascade('baz'));
        $this->assertEquals('qux', $collection->cascade('baz', 'qux'));
    }

    /** @test */
    function it_gets_and_sets_entry_blueprints()
    {
        BlueprintRepository::shouldReceive('find')->with('default')->andReturn($default = new Blueprint);
        BlueprintRepository::shouldReceive('find')->with('one')->andReturn($blueprintOne = new Blueprint);
        BlueprintRepository::shouldReceive('find')->with('two')->andReturn($blueprintTwo = new Blueprint);

        $collection = new Collection;
        $this->assertCount(0, $collection->entryBlueprints());
        $this->assertEquals($default, $collection->entryBlueprint());

        $return = $collection->entryBlueprints(['one', 'two']);

        $this->assertEquals($collection, $return);
        $blueprints = $collection->entryBlueprints();
        $this->assertCount(2, $blueprints);
        $this->assertEveryItemIsInstanceOf(Blueprint::class, $blueprints);
        $this->assertEquals([$blueprintOne, $blueprintTwo], $blueprints->values()->all());
        $this->assertEquals($blueprintOne, $collection->entryBlueprint());
    }

    /** @test */
    function it_gets_sort_field_and_direction()
    {
        $alpha = new Collection;
        $this->assertEquals('title', $alpha->sortField());
        $this->assertEquals('asc', $alpha->sortDirection());

        $dated = (new Collection)->dated(true);
        $this->assertEquals('date', $dated->sortField());
        $this->assertEquals('desc', $dated->sortDirection());

        $structureWithMaxDepthOfOne = $this->makeStructure()->maxDepth(1);
        $ordered = (new Collection)->structure($structureWithMaxDepthOfOne);
        $this->assertEquals('order', $ordered->sortField());
        $this->assertEquals('asc', $ordered->sortDirection());

        $datedAndOrdered = (new Collection)->dated(true)->structure($structureWithMaxDepthOfOne);
        $this->assertEquals('order', $datedAndOrdered->sortField());
        $this->assertEquals('asc', $datedAndOrdered->sortDirection());

        $structure = $this->makeStructure();
        $alpha->structure($structure);
        $this->assertEquals('title', $alpha->sortField());
        $this->assertEquals('asc', $alpha->sortDirection());
        $dated->structure($structure);
        $this->assertEquals('date', $dated->sortField());
        $this->assertEquals('desc', $dated->sortDirection());

        // TODO: Ability to control sort direction
    }

    /** @test */
    function it_saves_the_collection_through_the_api()
    {
        $collection = (new Collection)->handle('test');

        Facades\Collection::shouldReceive('save')->with($collection)->once();
        Facades\Blink::shouldReceive('flush')->with('collection-handles')->once();
        Facades\Blink::shouldReceive('flushStartingWith')->with('collection-test')->once();

        $return = $collection->save();

        $this->assertEquals($collection, $return);
    }

    /** @test */
    function it_sets_future_date_behavior()
    {
        $collection = (new Collection)->handle('test');
        $this->assertEquals('public', $collection->futureDateBehavior());

        $return = $collection->futureDateBehavior('private');
        $this->assertEquals($collection, $return);
        $this->assertEquals('private', $collection->futureDateBehavior());

        $return = $collection->futureDateBehavior(null);
        $this->assertEquals($collection, $return);
        $this->assertEquals('public', $collection->futureDateBehavior());
    }

    /** @test */
    function it_sets_past_date_behavior()
    {
        $collection = (new Collection)->handle('test');
        $this->assertEquals('public', $collection->pastDateBehavior());

        $return = $collection->pastDateBehavior('private');
        $this->assertEquals($collection, $return);
        $this->assertEquals('private', $collection->pastDateBehavior());

        $return = $collection->pastDateBehavior(null);
        $this->assertEquals($collection, $return);
        $this->assertEquals('public', $collection->pastDateBehavior());
    }

    /** @test */
    function it_gets_and_sets_the_default_publish_state()
    {
        $collection = (new Collection)->handle('test');
        $this->assertTrue($collection->defaultPublishState());

        $return = $collection->defaultPublishState(true);
        $this->assertEquals($collection, $return);
        $this->assertTrue($collection->defaultPublishState());

        $return = $collection->defaultPublishState(false);
        $this->assertEquals($collection, $return);
        $this->assertFalse($collection->defaultPublishState());
    }

    /** @test */
    function default_publish_state_is_always_false_when_using_revisions()
    {
        config(['statamic.revisions.enabled' => true]);

        $collection = (new Collection)->handle('test');
        $this->assertTrue($collection->defaultPublishState());

        $collection->revisionsEnabled(true);
        $this->assertFalse($collection->defaultPublishState());

        $collection->defaultPublishState(true);
        $this->assertFalse($collection->defaultPublishState());

        $collection->defaultPublishState(false);
        $this->assertFalse($collection->defaultPublishState());
    }

    /** @test */
    function it_sets_and_gets_structure()
    {
        $structure = new CollectionStructure;
        $collection = (new Collection)->handle('test');
        $this->assertFalse($collection->hasStructure());
        $this->assertNull($collection->structure());
        $this->assertNull($structure->handle());

        $collection->structure($structure);

        $this->assertTrue($collection->hasStructure());
        $this->assertSame($structure, $collection->structure());
        $this->assertEquals('collection::test', $structure->handle());
        $this->assertEquals('Test', $structure->title());
    }

    /** @test */
    function it_sets_the_structure_inline()
    {
        // This applies to a file-based approach.

        $collection = (new Collection)->handle('test');
        $this->assertFalse($collection->hasStructure());
        $this->assertNull($collection->structure());

        EntryFactory::id('123')->collection('test')->create();
        EntryFactory::id('456')->collection('test')->create();
        EntryFactory::id('789')->collection('test')->create();

        $return = $collection->structureContents($contents = [
            'max_depth' => 2,
            'tree' => [
                ['entry' => '123', 'children' => [
                    ['entry' => '789']
                ]],
                ['entry' => '456'],
            ]
        ]);

        $this->assertEquals($collection, $return);
        $this->assertEquals($contents, $collection->structureContents());
        $this->assertTrue($collection->hasStructure());
        $structure = $collection->structure();
        $this->assertInstanceOf(CollectionStructure::class, $structure);
        $this->assertEquals('collection::test', $structure->handle());
        $this->assertSame($collection, $structure->collection());
        $this->assertEquals(2, $structure->in('en')->pages()->all()->count());
        $this->assertEquals(3, $structure->in('en')->flattenedPages()->count());
        $this->assertEquals(2, $structure->maxDepth());
    }

    /** @test */
    function setting_a_structure_removes_the_existing_inline_structure()
    {
        $collection = (new Collection)->handle('test');
        $collection->structureContents($contents = ['tree' => []]);
        $this->assertSame($contents, $collection->structureContents());

        $collection->structure(new CollectionStructure);

        $this->assertNull($collection->structureContents());
    }

    /** @test */
    function setting_an_inline_structure_removes_the_existing_structure()
    {
        $collection = (new Collection)->handle('test');
        $collection->structure($structure = (new CollectionStructure)->maxDepth(2));
        $this->assertSame($structure, $collection->structure());
        $this->assertEquals(2, $collection->structure()->maxDepth());
        $this->assertNull($collection->structureContents());

        $collection->structureContents(['max_depth' => 13, 'tree' => []]);

        $this->assertNotSame($structure, $collection->structure());
        $this->assertEquals(13, $collection->structure()->maxDepth());
    }

    private function makeStructure()
    {
        return (new CollectionStructure)->tap(function ($s) {
            $s->addTree($s->makeTree('en'));
        });
    }
}
