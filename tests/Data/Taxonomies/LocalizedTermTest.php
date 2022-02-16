<?php

namespace Tests\Data\Taxonomies;

use BadMethodCallException;
use Facades\Statamic\Fields\BlueprintRepository;
use Mockery;
use Statamic\Facades;
use Statamic\Facades\Taxonomy;
use Statamic\Fields\Fieldtype;
use Statamic\Taxonomies\LocalizedTerm;
use Statamic\Taxonomies\Term;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class LocalizedTermTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function it_gets_the_entry_count_through_the_repository()
    {
        $term = (new Term)->taxonomy('tags')->slug('foo');
        $localized = new LocalizedTerm($term, 'en');

        $mock = \Mockery::mock(Facades\Term::getFacadeRoot())->makePartial();
        Facades\Term::swap($mock);
        $mock->shouldReceive('entriesCount')->with($localized)->andReturn(7)->once();

        $this->assertEquals(7, $localized->entriesCount());
        $this->assertEquals(7, $localized->entriesCount());
    }

    /** @test */
    public function if_the_value_is_explicitly_set_to_null_then_it_should_not_fall_back()
    {
        tap(Taxonomy::make('test')->sites(['en', 'fr']))->save();

        $term = (new Term)->taxonomy('test');

        $term->dataForLocale('en', [
            'one' => 'alfa',
            'two' => 'bravo',
            'three' => 'charlie',
        ]);

        $term->dataForLocale('fr', [
            'one' => 'delta',
            'two' => null,
        ]);

        $localized = $term->in('fr');

        $this->assertEquals([
            'one' => 'delta',
            'two' => null,
            'three' => 'charlie',
        ], $localized->values()->all());

        $this->assertEquals('delta', $localized->value('one'));
        $this->assertEquals(null, $localized->value('two'));
        $this->assertEquals('charlie', $localized->value('three'));
    }

    /** @test */
    public function it_gets_evaluated_augmented_value_using_magic_property()
    {
        (new class extends Fieldtype
        {
            protected static $handle = 'test';

            public function augment($value)
            {
                return $value.' (augmented)';
            }
        })::register();

        $blueprint = Facades\Blueprint::makeFromFields(['charlie' => ['type' => 'test']]);
        BlueprintRepository::shouldReceive('in')->with('taxonomies/tags')->andReturn(collect(['tags' => $blueprint]));
        Taxonomy::make('tags')->save();

        $term = (new Term)->taxonomy('tags')->slug('foo');

        $term->dataForLocale('en', [
            'alfa' => 'bravo',
            'charlie' => 'delta',
        ]);

        $localized = $term->in('en');

        $this->assertEquals('foo', $localized->slug);
        $this->assertEquals('bravo', $localized->alfa);
        $this->assertEquals('delta (augmented)', $localized->charlie);
    }

    /**
     * @test
     * @dataProvider queryBuilderProvider
     **/
    public function it_has_magic_property_and_methods_for_fields_that_augment_to_query_builders($builder)
    {
        $builder->shouldReceive('get')->once()->andReturn('query builder results');
        app()->instance('mocked-builder', $builder);

        (new class extends Fieldtype
        {
            protected static $handle = 'test';

            public function augment($value)
            {
                return app('mocked-builder');
            }
        })::register();

        $blueprint = Facades\Blueprint::makeFromFields(['foo' => ['type' => 'test']]);
        BlueprintRepository::shouldReceive('in')->with('taxonomies/tags')->andReturn(collect(['tags' => $blueprint]));
        Taxonomy::make('tags')->save();

        $term = (new Term)->taxonomy('tags');
        $term->dataForLocale('en', [
            'foo' => 'delta',
        ]);

        $localized = $term->in('en');

        $this->assertEquals('query builder results', $localized->foo);
        $this->assertSame($builder, $localized->foo());
    }

    public function queryBuilderProvider()
    {
        return [
            'statamic' => [Mockery::mock(\Statamic\Query\Builder::class)],
            'database' => [Mockery::mock(\Illuminate\Database\Query\Builder::class)],
            'eloquent' => [Mockery::mock(\Illuminate\Database\Eloquent\Builder::class)],
        ];
    }

    /** @test */
    public function calling_unknown_method_throws_exception()
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Call to undefined method Statamic\Taxonomies\LocalizedTerm::thisFieldDoesntExist()');

        Taxonomy::make('tags')->save();
        (new Term)->taxonomy('tags')->in('en')->thisFieldDoesntExist();
    }
}
