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
}
