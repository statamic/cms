<?php

namespace Tests\Data\Taxonomies;

use Facades\Statamic\Fields\BlueprintRepository;
use Statamic\Fields\Blueprint;
use Statamic\Taxonomies\Taxonomy;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class TaxonomyTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function it_stores_cascading_data_in_a_collection()
    {
        $taxonomy = new Taxonomy;
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $taxonomy->cascade());
        $this->assertTrue($taxonomy->cascade()->isEmpty());

        $taxonomy->cascade()->put('foo', 'bar');

        $this->assertTrue($taxonomy->cascade()->has('foo'));
        $this->assertEquals('bar', $taxonomy->cascade()->get('foo'));
    }

    /** @test */
    public function it_sets_all_the_cascade_data_when_passing_an_array()
    {
        $taxonomy = new Taxonomy;

        $return = $taxonomy->cascade($arr = ['foo' => 'bar', 'baz' => 'qux']);
        $this->assertEquals($taxonomy, $return);
        $this->assertEquals($arr, $taxonomy->cascade()->all());

        // test that passing an empty array is not treated as passing null
        $return = $taxonomy->cascade([]);
        $this->assertEquals($taxonomy, $return);
        $this->assertEquals([], $taxonomy->cascade()->all());
    }

    /** @test */
    public function it_gets_values_from_the_cascade_with_fallbacks()
    {
        $taxonomy = new Taxonomy;
        $taxonomy->cascade(['foo' => 'bar']);

        $this->assertEquals('bar', $taxonomy->cascade('foo'));
        $this->assertNull($taxonomy->cascade('baz'));
        $this->assertEquals('qux', $taxonomy->cascade('baz', 'qux'));
    }

    /** @test */
    public function it_gets_term_blueprints()
    {
        $taxonomy = (new Taxonomy)->handle('tags');

        BlueprintRepository::shouldReceive('in')->with('taxonomies/tags')->andReturn(collect([
            'one' => $blueprintOne = (new Blueprint)->setHandle('one'),
            'two' => $blueprintTwo = (new Blueprint)->setHandle('two'),
        ]));

        $blueprints = $taxonomy->termBlueprints();
        $this->assertCount(2, $blueprints);
        $this->assertEveryItemIsInstanceOf(Blueprint::class, $blueprints);
        $this->assertEquals([$blueprintOne, $blueprintTwo], $blueprints->all());

        $this->assertEquals($blueprintOne, $taxonomy->termBlueprint());
        $this->assertEquals($blueprintOne, $taxonomy->termBlueprint('one'));
        $this->assertEquals($blueprintTwo, $taxonomy->termBlueprint('two'));
        $this->assertNull($taxonomy->termBlueprint('three'));
    }

    /** @test */
    public function no_existing_blueprints_will_fall_back_to_a_default_named_after_the_taxonomy()
    {
        $taxonomy = (new Taxonomy)->handle('tags');

        BlueprintRepository::shouldReceive('in')->with('taxonomies/tags')->andReturn(collect());
        BlueprintRepository::shouldReceive('find')->with('default')->andReturn(
            $blueprint = (new Blueprint)
                ->setHandle('thisll_change')
                ->setContents(['title' => 'This will change'])
        );

        $blueprints = $taxonomy->termBlueprints();
        $this->assertCount(1, $blueprints);
        $this->assertEquals([$blueprint], $blueprints->all());

        tap($taxonomy->termBlueprint(), function ($default) use ($blueprint) {
            $this->assertEquals($blueprint, $default);
            $this->assertEquals('tags', $default->handle());
            $this->assertEquals('Tags', $default->title());
        });

        $this->assertEquals($blueprint, $taxonomy->termBlueprint('tags'));
        $this->assertNull($taxonomy->termBlueprint('two'));
    }

    /** @test */
    public function it_returns_a_url_for_breadcrumbs()
    {
        $taxonomy = (new Taxonomy)->handle('tags');
        $this->assertEquals('/tags', $taxonomy->url());
    }

    /** @test */
    public function it_gets_and_sets_supplemental_data()
    {
        $taxonomy = (new Taxonomy)->handle('tags');

        $return = $taxonomy->setSupplement('foo', 'bar');

        $this->assertEquals($taxonomy, $return);
        $this->assertEquals('bar', $taxonomy->getSupplement('foo'));
    }
}
