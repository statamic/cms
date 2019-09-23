<?php

namespace Tests\Data\Entries;

use Statamic\Facades;
use Tests\TestCase;
use Statamic\Facades\Site;
use Statamic\Fields\Blueprint;
use Statamic\Entries\Entry;
use Statamic\Entries\Collection;
use Tests\PreventSavingStacheItemsToDisk;
use Facades\Statamic\Fields\BlueprintRepository;

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
    function it_gets_and_sets_the_route()
    {
        $collection = new Collection;
        $this->assertNull($collection->route());

        $return = $collection->route('{slug}');

        $this->assertEquals($collection, $return);
        $this->assertEquals('{slug}', $collection->route());
    }

    /** @test */
    function it_gets_and_sets_the_template()
    {
        config(['statamic.theming.views.entry' => 'post']);

        $collection = new Collection;
        $this->assertEquals('post', $collection->template());

        $return = $collection->template('foo');

        $this->assertEquals($collection, $return);
        $this->assertEquals('foo', $collection->template());
    }

    /** @test */
    function it_gets_and_sets_the_layout()
    {
        config(['statamic.theming.views.layout' => 'default']);

        $collection = new Collection;
        $this->assertEquals('default', $collection->layout());

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
        $this->assertEquals([], $sites->all());

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
    function it_sets_and_gets_data_values()
    {
        $collection = new Collection;
        $this->assertNull($collection->get('foo'));

        $return = $collection->set('foo', 'bar');

        $this->assertEquals($collection, $return);
        $this->assertTrue($collection->has('foo'));
        $this->assertEquals('bar', $collection->get('foo'));
    }

    /** @test */
    function it_gets_and_sets_all_data()
    {
        $collection = new Collection;
        $this->assertEquals([], $collection->data());

        $return = $collection->data(['foo' => 'bar']);

        $this->assertEquals($collection, $return);
        $this->assertEquals(['foo' => 'bar'], $collection->data());
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

        $ordered = (new Collection)->orderable(true);
        $this->assertEquals('order', $ordered->sortField());
        $this->assertEquals('asc', $ordered->sortDirection());

        $datedAndOrdered = (new Collection)->dated(true)->orderable(true);
        $this->assertEquals('order', $datedAndOrdered->sortField());
        $this->assertEquals('asc', $datedAndOrdered->sortDirection());

        // TODO: Ability to control sort direction
    }

    /** @test */
    function it_saves_the_collection_through_the_api()
    {
        $collection = new Collection;

        Facades\Collection::shouldReceive('save')->with($collection)->once();

        $return = $collection->save();

        $this->assertEquals($collection, $return);
    }

    /** @test */
    function entry_can_be_ordered()
    {
        $collection = (new Collection)->handle('test')->setEntryPositions([]);

        $return = $collection->setEntryPosition('one', 3);
        $this->assertEquals($collection, $return);
        $this->assertSame([3 => 'one'], $collection->getEntryPositions());
        $this->assertSame(['one'], $collection->getEntryOrder());
        $this->assertEquals(1, $collection->getEntryOrder('one'));

        $collection->setEntryPosition('two', 7);
        $this->assertSame([3 => 'one', 7 => 'two'], $collection->getEntryPositions());
        $this->assertSame(['one', 'two'], $collection->getEntryOrder());
        $this->assertEquals(1, $collection->getEntryOrder('one'));
        $this->assertEquals(2, $collection->getEntryOrder('two'));

        $collection->setEntryPosition('three', 5);
        $this->assertSame([3 => 'one', 5 => 'three', 7 => 'two'], $collection->getEntryPositions());
        $this->assertSame(['one', 'three', 'two'], $collection->getEntryOrder());
        $this->assertEquals(1, $collection->getEntryOrder('one'));
        $this->assertEquals(3, $collection->getEntryOrder('two'));
        $this->assertEquals(2, $collection->getEntryOrder('three'));

        $collection->setEntryPosition('four', 1);
        $this->assertSame([1 => 'four', 3 => 'one', 5 => 'three', 7 => 'two'], $collection->getEntryPositions());
        $this->assertSame(['four', 'one', 'three', 'two'], $collection->getEntryOrder());
        $this->assertEquals(2, $collection->getEntryOrder('one'));
        $this->assertEquals(4, $collection->getEntryOrder('two'));
        $this->assertEquals(3, $collection->getEntryOrder('three'));
        $this->assertEquals(1, $collection->getEntryOrder('four'));

        $this->assertNull($collection->getEntryPosition('unknown'));
        $this->assertNull($collection->getEntryOrder('unknown'));
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
}
