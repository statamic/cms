<?php

namespace Tests\Data\Taxonomies;

use BadMethodCallException;
use Facades\Statamic\Fields\BlueprintRepository;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Event;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Events\LocalizedTermDeleted;
use Statamic\Events\LocalizedTermSaved;
use Statamic\Facades;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Taxonomy;
use Statamic\Fields\Fieldtype;
use Statamic\Fields\Value;
use Statamic\Support\Arr;
use Statamic\Taxonomies\LocalizedTerm;
use Statamic\Taxonomies\Term;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class LocalizedTermTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_gets_the_reference()
    {
        $term = (new Term)->taxonomy('tags')->slug('foo');

        $this->assertEquals('term::tags::foo::en', (new LocalizedTerm($term, 'en'))->reference());
        $this->assertEquals('term::tags::foo::fr', (new LocalizedTerm($term, 'fr'))->reference());
    }

    #[Test]
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

    #[Test]
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

    #[Test]
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
        $this->assertEquals('foo', $localized['slug']);
        $this->assertEquals('bravo', $localized->alfa);
        $this->assertEquals('bravo', $localized['alfa']);
        $this->assertEquals('delta (augmented)', $localized->charlie);
        $this->assertEquals('delta (augmented)', $localized['charlie']);
    }

    #[Test]
    #[DataProvider('queryBuilderProvider')]
    public function it_has_magic_property_and_methods_for_fields_that_augment_to_query_builders($builder)
    {
        $builder->shouldReceive('get')->times(2)->andReturn('query builder results');
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
        $this->assertEquals('query builder results', $localized['foo']);
        $this->assertSame($builder, $localized->foo());
    }

    public static function queryBuilderProvider()
    {
        return [
            'statamic' => [Mockery::mock(\Statamic\Query\Builder::class)],
            'database' => [Mockery::mock(\Illuminate\Database\Query\Builder::class)],
            'eloquent' => [Mockery::mock(\Illuminate\Database\Eloquent\Builder::class)],
        ];
    }

    #[Test]
    public function calling_unknown_method_throws_exception()
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Call to undefined method Statamic\Taxonomies\LocalizedTerm::thisFieldDoesntExist()');

        Taxonomy::make('tags')->save();
        (new Term)->taxonomy('tags')->in('en')->thisFieldDoesntExist();
    }

    #[Test]
    public function it_converts_to_an_array()
    {
        $fieldtype = new class extends Fieldtype
        {
            protected static $handle = 'test';

            public function augment($value)
            {
                return [
                    new Value('alfa'),
                    new Value([
                        new Value('bravo'),
                        new Value('charlie'),
                        'delta',
                    ]),
                ];
            }
        };
        $fieldtype::register();

        $blueprint = Blueprint::makeFromFields([
            'baz' => [
                'type' => 'test',
            ],
        ]);
        BlueprintRepository::shouldReceive('in')->with('taxonomies/tags')->andReturn(collect([
            'tags' => $blueprint->setHandle('tags'),
        ]));

        Taxonomy::make('tags')->save();
        $term = (new Term)->taxonomy('tags')->slug('test')->dataForLocale('en', [
            'foo' => 'bar',
            'baz' => 'qux',
        ])->in('en');

        $this->assertInstanceOf(Arrayable::class, $term);

        $array = $term->toArray();
        $this->assertEquals($term->augmented()->keys(), array_keys($array));
        $this->assertEquals([
            'alfa',
            [
                'bravo',
                'charlie',
                'delta',
            ],
        ], $array['baz'], 'Value objects are not resolved recursively');

        $array = $term
            ->selectedQueryColumns($keys = ['id', 'foo', 'baz'])
            ->toArray();

        $this->assertEquals($keys, array_keys($array), 'toArray keys differ from selectedQueryColumns');
    }

    #[Test]
    public function only_requested_relationship_fields_are_included_in_to_array()
    {
        $regularFieldtype = new class extends Fieldtype
        {
            protected static $handle = 'regular';

            public function augment($value)
            {
                return 'augmented '.$value;
            }
        };
        $regularFieldtype::register();

        $relationshipFieldtype = new class extends Fieldtype
        {
            protected static $handle = 'relationship';
            protected $relationship = true;

            public function augment($values)
            {
                return collect($values)->map(fn ($value) => 'augmented '.$value)->all();
            }
        };
        $relationshipFieldtype::register();

        $blueprint = Blueprint::makeFromFields([
            'alfa' => ['type' => 'regular'],
            'bravo' => ['type' => 'relationship'],
            'charlie' => ['type' => 'relationship'],
        ]);
        BlueprintRepository::shouldReceive('in')->with('taxonomies/tags')->andReturn(collect([
            'tags' => $blueprint->setHandle('tags'),
        ]));

        Taxonomy::make('tags')->save();
        $term = (new Term)->taxonomy('tags')->slug('test')->dataForLocale('en', [
            'alfa' => 'one',
            'bravo' => ['a', 'b'],
            'charlie' => ['c', 'd'],
        ])->in('en');

        $this->assertEquals([
            'alfa' => 'augmented one',
            'bravo' => ['a', 'b'],
            'charlie' => ['augmented c', 'augmented d'],
        ], Arr::only($term->selectedQueryRelations(['charlie'])->toArray(), ['alfa', 'bravo', 'charlie']));
    }

    #[Test]
    public function it_dispatches_localized_term_saved_event()
    {
        Event::fake();

        Taxonomy::make('tags')->save();

        $term = Facades\Term::make()->taxonomy('tags')->slug('foo');
        $localized = tap($term->in('en')->set('title', 'foo'))->save();

        Event::assertDispatched(LocalizedTermSaved::class, fn ($event) => $event->term === $localized);
    }

    #[Test]
    public function it_dispatches_localized_term_deleted_event()
    {
        Event::fake();

        Taxonomy::make('tags')->save();

        $term = Facades\Term::make()->taxonomy('tags')->slug('foo');
        $localized = tap($term->in('en')->set('title', 'foo'))->save();

        $localized->delete();

        Event::assertDispatched(LocalizedTermDeleted::class, fn ($event) => $event->term === $localized);
    }
}
