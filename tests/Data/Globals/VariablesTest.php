<?php

namespace Tests\Data\Globals;

use BadMethodCallException;
use Facades\Statamic\Fields\BlueprintRepository;
use Illuminate\Contracts\Support\Arrayable;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades;
use Statamic\Facades\Blueprint;
use Statamic\Facades\GlobalSet;
use Statamic\Fields\Fieldtype;
use Statamic\Fields\Value;
use Statamic\Globals\Variables;
use Statamic\Support\Arr;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class VariablesTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_gets_file_contents_for_saving()
    {
        $global = GlobalSet::make('test');

        $entry = $global->inDefaultSite()->data([
            'array' => ['first one', 'second one'],
            'string' => 'The string',
            'null' => null, // this...
            'empty' => [],  // and this should get stripped out because it's the root. there's no origin to fall back to.
        ]);

        $expected = <<<'EOT'
array:
  - 'first one'
  - 'second one'
string: 'The string'

EOT;
        $this->assertEquals($expected, $entry->fileContents());
    }

    #[Test]
    public function it_gets_file_contents_for_saving_a_localized_set()
    {
        $this->setSites([
            'a' => ['url' => '/', 'locale' => 'en'],
            'b' => ['url' => '/b/', 'locale' => 'fr'],
            'c' => ['url' => '/b/', 'locale' => 'fr'],
            'd' => ['url' => '/d/', 'locale' => 'fr'],
        ]);

        $global = GlobalSet::make('test')->sites([
            'a',
            'b' => 'a',
            'c',
        ])->save();

        $a = $global->in('a')->data([
            'array' => ['first one', 'second one'],
            'string' => 'The string',
            'null' => null, // this...
            'empty' => [],  // and this should get stripped out because there's no origin to fall back to.
        ])->save();

        $b = $global->in('b')->data([
            'array' => ['first one', 'second one'],
            'string' => 'The string',
            'null' => null, // this...
            'empty' => [],  // and this should not get stripped out, otherwise it would fall back to the origin.
        ])->save();

        $c = $global->in('c')->data([
            'array' => ['first one', 'second one'],
            'string' => 'The string',
            'null' => null, // this...
            'empty' => [],  // and this should get stripped out because there's no origin to fall back to.
        ])->save();

        $expected = <<<'EOT'
array:
  - 'first one'
  - 'second one'
string: 'The string'

EOT;
        $this->assertEquals($expected, $a->fileContents());

        $expected = <<<'EOT'
array:
  - 'first one'
  - 'second one'
string: 'The string'
'null': null
empty: {  }

EOT;
        $this->assertEquals($expected, $b->fileContents());

        $expected = <<<'EOT'
array:
  - 'first one'
  - 'second one'
string: 'The string'

EOT;
        $this->assertEquals($expected, $c->fileContents());
    }

    #[Test]
    public function if_the_value_is_explicitly_set_to_null_then_it_should_not_fall_back()
    {
        $this->setSites([
            'a' => ['url' => '/', 'locale' => 'en'],
            'b' => ['url' => '/b/', 'locale' => 'fr'],
            'c' => ['url' => '/b/', 'locale' => 'fr'],
            'd' => ['url' => '/d/', 'locale' => 'fr'],
        ]);

        $global = GlobalSet::make('test')->sites([
            'a',
            'b' => 'a',
            'c' => 'b',
            'd',
            'e' => 'd',
        ])->save();

        $a = $global->in('a')->data([
            'one' => 'alfa',
            'two' => 'bravo',
            'three' => 'charlie',
            'four' => 'delta',
        ])->save();

        // originates from a
        $b = $global->in('b')->data([
            'one' => 'echo',
            'two' => null,
        ])->save();

        // originates from b, which originates from a
        $c = $global->in('c')->data([
            'three' => 'foxtrot',
        ])->save();

        // does not originate from anything
        $d = $global->in('d')->data([
            'one' => 'golf',
            'two' => 'hotel',
            'three' => 'india',
        ])->save();

        // originates from d. just to test that it doesn't unintentionally fall back to the default/first.
        $e = $global->in('e')->data([
            'one' => 'juliett',
            'two' => null,
        ])->save();

        $this->assertEquals([
            'one' => 'alfa',
            'two' => 'bravo',
            'three' => 'charlie',
            'four' => 'delta',
        ], $a->values()->all());
        $this->assertEquals('alfa', $a->value('one'));
        $this->assertEquals('bravo', $a->value('two'));
        $this->assertEquals('charlie', $a->value('three'));
        $this->assertEquals('delta', $a->value('four'));

        $this->assertEquals([
            'one' => 'echo',
            'two' => null,
            'three' => 'charlie',
            'four' => 'delta',
        ], $b->values()->all());
        $this->assertEquals('echo', $b->value('one'));
        $this->assertEquals(null, $b->value('two'));
        $this->assertEquals('charlie', $b->value('three'));
        $this->assertEquals('delta', $b->value('four'));

        $this->assertEquals([
            'one' => 'echo',
            'two' => null,
            'three' => 'foxtrot',
            'four' => 'delta',
        ], $c->values()->all());
        $this->assertEquals('echo', $c->value('one'));
        $this->assertEquals(null, $c->value('two'));
        $this->assertEquals('foxtrot', $c->value('three'));
        $this->assertEquals('delta', $c->value('four'));

        $this->assertEquals([
            'one' => 'golf',
            'two' => 'hotel',
            'three' => 'india',
        ], $d->values()->all());
        $this->assertEquals('golf', $d->value('one'));
        $this->assertEquals('hotel', $d->value('two'));
        $this->assertEquals('india', $d->value('three'));
        $this->assertEquals(null, $d->value('four'));

        $this->assertEquals([
            'one' => 'juliett',
            'two' => null,
            'three' => 'india',
        ], $e->values()->all());
        $this->assertEquals('juliett', $e->value('one'));
        $this->assertEquals(null, $e->value('two'));
        $this->assertEquals('india', $e->value('three'));
        $this->assertEquals(null, $e->value('four'));
    }

    #[Test]
    public function it_sets_data_values_using_magic_properties()
    {
        $variables = new Variables;
        $this->assertNull($variables->get('foo'));

        $variables->foo = 'bar';

        $this->assertTrue($variables->has('foo'));
        $this->assertEquals('bar', $variables->get('foo'));
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
        BlueprintRepository::shouldReceive('find')->with('globals.settings')->andReturn($blueprint);
        $global = GlobalSet::make('settings');
        $variables = $global->in('en');
        $variables->set('alfa', 'bravo');
        $variables->set('charlie', 'delta');

        $this->assertEquals('bravo', $variables->alfa);
        $this->assertEquals('bravo', $variables['alfa']);
        $this->assertEquals('delta (augmented)', $variables->charlie);
        $this->assertEquals('delta (augmented)', $variables['charlie']);
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
        BlueprintRepository::shouldReceive('find')->with('globals.settings')->andReturn($blueprint);
        $global = GlobalSet::make('settings');
        $variables = $global->in('en');
        $variables->set('foo', 'delta');

        $this->assertEquals('query builder results', $variables->foo);
        $this->assertEquals('query builder results', $variables['foo']);
        $this->assertSame($builder, $variables->foo());
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
        $this->expectExceptionMessage('Call to undefined method Statamic\Globals\Variables::thisFieldDoesntExist()');

        GlobalSet::make('settings')->in('en')->thisFieldDoesntExist();
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
        BlueprintRepository::shouldReceive('find')->with('globals.settings')->andReturn($blueprint);
        $global = GlobalSet::make('settings');
        $variables = $global->in('en');
        $variables->set('foo', 'bar');
        $variables->set('baz', 'qux');

        $this->assertInstanceOf(Arrayable::class, $variables);

        $array = $variables->toArray();
        $this->assertEquals($variables->augmented()->keys(), array_keys($array));
        $this->assertEquals([
            'alfa',
            [
                'bravo',
                'charlie',
                'delta',
            ],
        ], $array['baz'], 'Value objects are not resolved recursively');
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
        BlueprintRepository::shouldReceive('find')->with('globals.settings')->andReturn($blueprint);
        $global = GlobalSet::make('settings');
        $variables = $global->in('en');
        $variables->set('alfa', 'one');
        $variables->set('bravo', ['a', 'b']);
        $variables->set('charlie', ['c', 'd']);

        $this->assertEquals([
            'alfa' => 'augmented one',
            'bravo' => ['a', 'b'],
            'charlie' => ['augmented c', 'augmented d'],
        ], Arr::only($variables->selectedQueryRelations(['charlie'])->toArray(), ['alfa', 'bravo', 'charlie']));
    }
}
