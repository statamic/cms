<?php

namespace Tests\Data\Globals;

use BadMethodCallException;
use Facades\Statamic\Fields\BlueprintRepository;
use Mockery;
use Statamic\Facades;
use Statamic\Facades\GlobalSet;
use Statamic\Fields\Fieldtype;
use Statamic\Globals\Variables;
use Tests\TestCase;

class VariablesTest extends TestCase
{
    /** @test */
    public function it_gets_file_contents_for_saving()
    {
        $entry = (new Variables)->data([
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

    /** @test */
    public function it_gets_file_contents_for_saving_a_localized_set()
    {
        $global = GlobalSet::make('test');

        $a = $global->makeLocalization('a')->data([
            'array' => ['first one', 'second one'],
            'string' => 'The string',
            'null' => null, // this...
            'empty' => [],  // and this should get stripped out because there's no origin to fall back to.
        ]);

        $b = $global->makeLocalization('b')->origin($a)->data([
            'array' => ['first one', 'second one'],
            'string' => 'The string',
            'null' => null, // this...
            'empty' => [],  // and this should not get stripped out, otherwise it would fall back to the origin.
        ]);

        $c = $global->makeLocalization('c')->data([
            'array' => ['first one', 'second one'],
            'string' => 'The string',
            'null' => null, // this...
            'empty' => [],  // and this should get stripped out because there's no origin to fall back to.
        ]);

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
origin: a

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

    /** @test */
    public function if_the_value_is_explicitly_set_to_null_then_it_should_not_fall_back()
    {
        $global = GlobalSet::make('test');

        $a = $global->makeLocalization('a')->data([
            'one' => 'alfa',
            'two' => 'bravo',
            'three' => 'charlie',
            'four' => 'delta',
        ]);

        // originates from a
        $b = $global->makeLocalization('b')->origin($a)->data([
            'one' => 'echo',
            'two' => null,
        ]);

        // originates from b, which originates from a
        $c = $global->makeLocalization('c')->origin($b)->data([
            'three' => 'foxtrot',
        ]);

        // does not originate from anything
        $d = $global->makeLocalization('d')->data([
            'one' => 'golf',
            'two' => 'hotel',
            'three' => 'india',
        ]);

        // originates from d. just to test that it doesn't unintentionally fall back to the default/first.
        $e = $global->makeLocalization('e')->origin($d)->data([
            'one' => 'juliett',
            'two' => null,
        ]);

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

    /** @test */
    public function it_sets_data_values_using_magic_properties()
    {
        $variables = new Variables;
        $this->assertNull($variables->get('foo'));

        $variables->foo = 'bar';

        $this->assertTrue($variables->has('foo'));
        $this->assertEquals('bar', $variables->get('foo'));
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
        BlueprintRepository::shouldReceive('find')->with('globals.settings')->andReturn($blueprint);
        $global = GlobalSet::make('settings');
        $variables = $global->makeLocalization('en');
        $variables->set('alfa', 'bravo');
        $variables->set('charlie', 'delta');

        $this->assertEquals('bravo', $variables->alfa);
        $this->assertEquals('delta (augmented)', $variables->charlie);
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
        BlueprintRepository::shouldReceive('find')->with('globals.settings')->andReturn($blueprint);
        $global = GlobalSet::make('settings');
        $variables = $global->makeLocalization('en');
        $variables->set('foo', 'delta');

        $this->assertEquals('query builder results', $variables->foo);
        $this->assertSame($builder, $variables->foo());
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
        $this->expectExceptionMessage('Call to undefined method Statamic\Globals\Variables::thisFieldDoesntExist()');

        GlobalSet::make('settings')->makeLocalization('en')->thisFieldDoesntExist();
    }
}
