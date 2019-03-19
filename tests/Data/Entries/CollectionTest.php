<?php

namespace Tests\Data\Entries;

use Statamic\API;
use Tests\TestCase;
use Statamic\Fields\Blueprint;
use Statamic\Data\Entries\Entry;
use Statamic\Data\Entries\Collection;
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
    function it_gets_and_sets_the_sites_it_can_be_used_in()
    {
        $collection = new Collection;
        $this->assertCount(0, $collection->sites());

        $return = $collection->sites(['en', 'fr']);

        $this->assertEquals($collection, $return);
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $collection->sites());
        $this->assertEquals(['en', 'fr'], $collection->sites()->all());
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
    function it_gets_and_sets_the_order_and_defaults_to_alphabetical()
    {
        $collection = new Collection;
        $this->assertEquals('alphabetical', $collection->order());

        $return = $collection->order('number');

        $this->assertEquals($collection, $return);
        $this->assertEquals('number', $collection->order());
    }

    /** @test */
    function it_corrects_numericish_order_values_for_convenience___youre_welcome()
    {
        $collection = new Collection;

        foreach (['number', 'numeric', 'numerical', 'numbers', 'numbered'] as $order) {
            $collection->order($order);
            $this->assertEquals('number', $collection->order());
        }
    }

    /** @test */
    function it_gets_sort_field_and_direction()
    {
        $collection = new Collection;
        $this->assertEquals('title', $collection->sortField());
        $this->assertEquals('asc', $collection->sortDirection());

        $collection->order('date');
        $this->assertEquals('date', $collection->sortField());
        $this->assertEquals('desc', $collection->sortDirection());

        $collection->order('number');
        $this->assertEquals('order', $collection->sortField());
        $this->assertEquals('asc', $collection->sortDirection());

        // TODO: Ability to control sort direction
    }

    /** @test */
    function it_saves_the_collection_through_the_api()
    {
        $collection = new Collection;

        API\Collection::shouldReceive('save')->with($collection)->once();

        $return = $collection->save();

        $this->assertEquals($collection, $return);
    }

    /** @test */
    function it_gets_the_path()
    {
        config(['statamic.stache.stores.collections.directory' => '/path/to/collections']);

        $collection = (new Collection)->handle('test');

        $this->assertEquals('/path/to/collections/test.yaml', $collection->path());
    }
}
