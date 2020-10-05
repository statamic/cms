<?php

namespace Tests\Data\Taxonomies;

use Facades\Statamic\Fields\BlueprintRepository;
use Mockery;
use Statamic\Facades\Taxonomy;
use Statamic\Fields\Blueprint;
use Statamic\Taxonomies\Term;
use Statamic\Testing\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class TermTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function it_gets_the_blueprint_when_defined_on_itself()
    {
        BlueprintRepository::shouldReceive('in')->with('taxonomies/tags')->andReturn(collect([
            'first' => $first = (new Blueprint)->setHandle('first'),
            'second' => $second = (new Blueprint)->setHandle('second'),
        ]));
        Taxonomy::make('tags')->save();
        $term = (new Term)->taxonomy('tags')->blueprint('second');

        $this->assertSame($second, $term->blueprint());
        $this->assertNotSame($first, $second);
    }

    /** @test */
    public function it_gets_the_blueprint_when_defined_in_a_value()
    {
        BlueprintRepository::shouldReceive('in')->with('taxonomies/tags')->andReturn(collect([
            'first' => $first = (new Blueprint)->setHandle('first'),
            'second' => $second = (new Blueprint)->setHandle('second'),
        ]));
        Taxonomy::make('tags')->save();
        $term = (new Term)->taxonomy('tags')->set('blueprint', 'second');

        $this->assertSame($second, $term->blueprint());
        $this->assertNotSame($first, $second);
    }

    /** @test */
    public function it_gets_the_default_taxonomy_blueprint_when_undefined()
    {
        BlueprintRepository::shouldReceive('in')->with('taxonomies/tags')->andReturn(collect([
            'first' => $first = (new Blueprint)->setHandle('first'),
            'second' => $second = (new Blueprint)->setHandle('second'),
        ]));
        $taxonomy = tap(Taxonomy::make('tags'))->save();
        $term = (new Term)->taxonomy($taxonomy);

        $this->assertSame($first, $term->blueprint());
        $this->assertNotSame($first, $second);
    }

    /** @test */
    public function the_blueprint_is_blinked_when_getting_and_flushed_when_setting()
    {
        $term = (new Term)->taxonomy('tags');
        $taxonomy = Mockery::mock(Taxonomy::make('tags'));
        $taxonomy->shouldReceive('termBlueprint')->with(null, $term)->once()->andReturn('the old blueprint');
        $taxonomy->shouldReceive('termBlueprint')->with('new', $term)->once()->andReturn('the new blueprint');
        Taxonomy::shouldReceive('findByHandle')->with('tags')->andReturn($taxonomy);

        $this->assertEquals('the old blueprint', $term->blueprint());
        $this->assertEquals('the old blueprint', $term->blueprint());

        $term->blueprint('new');

        $this->assertEquals('the new blueprint', $term->blueprint());
        $this->assertEquals('the new blueprint', $term->blueprint());
    }
}
