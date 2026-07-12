<?php

namespace Tests\Data;

use Facades\Statamic\Fields\FieldtypeRepository;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Data\AbstractAugmented;
use Statamic\Data\AugmentedCollection;
use Statamic\Data\ContainsData;
use Statamic\Facades\Blueprint;
use Statamic\Fields\Fieldtype;
use Statamic\Fields\Value;
use Tests\TestCase;

class AugmentedTest extends TestCase
{
    private $thing;
    private $blueprintThing;

    public function setUp(): void
    {
        parent::setUp();

        $this->thing = new Thing($data = [
            'foo' => 'bar',
            'baz' => 'qux',
        ]);

        $this->blueprintThing = new BlueprintThing($data);
    }

    private function assertEqualsValue($expected, $actual)
    {
        $this->assertInstanceOf(Value::class, $actual);
        $this->assertEquals($expected, $actual->value());
    }

    private function assertNullValue($actual)
    {
        $this->assertInstanceOf(Value::class, $actual);
        $this->assertNull($actual->value());
    }

    #[Test]
    public function it_gets_a_single_value_by_key()
    {
        $augmented = new class($this->thing) extends BaseAugmentedThing
        {
            //
        };

        $this->assertEqualsValue('bar', $augmented->get('foo'));
        $this->assertNullValue($augmented->get('unknown'));
    }

    #[Test]
    public function it_can_use_null_as_a_supplement_value()
    {
        $augmented = new class($this->thing) extends BaseAugmentedThing
        {
            //
        };

        $this->assertEqualsValue('bar', $augmented->get('foo'));

        $this->thing->setSupplement('foo', null);

        $this->assertNullValue($augmented->get('foo'));
    }

    #[Test]
    public function it_gets_a_single_value_by_key_using_the_value_method_if_it_exists()
    {
        $thingWithValueMethod = new class($this->thing->data()) extends Thing
        {
            public function value($key)
            {
                return $this->get($key) ? $this->get($key).' (value)' : null;
            }
        };

        $augmented = new class($thingWithValueMethod) extends BaseAugmentedThing
        {
            //
        };

        $this->assertEqualsValue('bar (value)', $augmented->get('foo'));
        $this->assertNullValue($augmented->get('unknown'));
    }

    #[Test]
    public function it_gets_a_value_from_the_thing_if_theres_a_corresponding_method_for_a_key()
    {
        $augmented = new class($this->thing) extends BaseAugmentedThing
        {
            public function keys()
            {
                return ['slug', 'the_slug'];
            }
        };

        $this->assertEqualsValue('the-thing', $augmented->get('slug'));
        $this->assertEqualsValue('the-thing', $augmented->get('the_slug'));
        $this->assertEqualsValue('the-thing', $augmented->get('theSlug'));
        $this->assertNullValue($augmented->get('cant_call_me'));
        $this->assertNullValue($augmented->get('cantCallMe'));
    }

    #[Test]
    public function it_gets_a_value_from_the_augmented_thing_if_theres_a_method()
    {
        $augmented = new class($this->thing) extends BaseAugmentedThing
        {
            public function slug()
            {
                return 'the-augmented-thing';
            }

            public function theSlug()
            {
                return $this->slug();
            }
        };

        $this->assertEqualsValue('the-augmented-thing', $augmented->get('slug'));
        $this->assertEqualsValue('the-augmented-thing', $augmented->get('the_slug'));
        $this->assertEqualsValue('the-augmented-thing', $augmented->get('theSlug'));
    }

    #[Test]
    public function if_an_augmented_things_method_returns_a_value_instance_then_use_it()
    {
        // An example of this would be the AugmentedEntry::authors() method.

        app()->instance('foo-return-value', $valueInstance = new Value('something completely custom'));

        $augmented = new class($this->thing) extends BaseAugmentedThing
        {
            public function foo()
            {
                return app('foo-return-value');
            }
        };

        // Don't really care if it's literally the same object, just that it's the appropriate result.
        $this->assertEquals($valueInstance->value(), $augmented->get('foo')->value());
    }

    #[Test]
    public function the_value_object_returned_contains_appropriate_fieldtype_if_the_thing_has_a_blueprint_and_theres_a_matching_field()
    {
        FieldtypeRepository::shouldReceive('find')->with('test')
            ->andReturn($fieldtype = new class extends Fieldtype
            {
                public function augment($value)
                {
                    return 'AUGMENTED '.strtoupper($value);
                }
            });

        $augmented = new class($this->blueprintThing) extends BaseAugmentedThing
        {
            public function keys()
            {
                return array_merge(parent::keys(), ['hello', 'slug', 'the_slug']);
            }

            public function hello()
            {
                return 'world';
            }
        };

        tap($augmented->get('foo'), function ($value) use ($fieldtype) {
            $this->assertInstanceOf(Value::class, $value);
            $this->assertEquals('bar', $value->raw());
            $this->assertEquals('AUGMENTED BAR', $value->value());
            $this->assertEquals('foo', $value->handle());
            $this->assertEquals($fieldtype, $value->fieldtype());
            $this->assertEquals($this->blueprintThing, $value->augmentable());
        });

        tap($augmented->get('slug'), function ($value) use ($fieldtype) {
            $this->assertInstanceOf(Value::class, $value);
            $this->assertEquals('the-thing', $value->raw());
            $this->assertEquals('AUGMENTED THE-THING', $value->value());
            $this->assertEquals('slug', $value->handle());
            $this->assertEquals($fieldtype, $value->fieldtype());
            $this->assertEquals($this->blueprintThing, $value->augmentable());
        });

        tap($augmented->get('baz'), function ($value) {
            $this->assertInstanceOf(Value::class, $value);
            $this->assertEquals('qux', $value->raw());
            $this->assertEquals('qux', $value->value());
            $this->assertEquals('baz', $value->handle());
            $this->assertNull($value->fieldtype());
            $this->assertEquals($this->blueprintThing, $value->augmentable());
        });

        tap($augmented->get('the_slug'), function ($value) {
            $this->assertInstanceOf(Value::class, $value);
            $this->assertEquals('the-thing', $value->raw());
            $this->assertEquals('the-thing', $value->value());
            $this->assertEquals('the_slug', $value->handle());
            $this->assertNull($value->fieldtype());
            $this->assertEquals($this->blueprintThing, $value->augmentable());
        });

        tap($augmented->get('hello'), function ($value) {
            $this->assertInstanceOf(Value::class, $value);
            $this->assertEquals('world', $value->raw());
            $this->assertEquals('world', $value->value());
            $this->assertEquals('hello', $value->handle());
            $this->assertNull($value->fieldtype());
            $this->assertEquals($this->blueprintThing, $value->augmentable());
        });

        tap($augmented->get('unknown'), function ($value) {
            $this->assertInstanceOf(Value::class, $value);
            $this->assertNull($value->raw());
            $this->assertNull($value->value());
            $this->assertEquals('unknown', $value->handle());
            $this->assertNull($value->fieldtype());
            $this->assertEquals($this->blueprintThing, $value->augmentable());
        });
    }

    #[Test]
    public function if_the_augmented_thing_has_a_method_with_a_corresponding_blueprint_field_it_will_not_use_that_fieldtype()
    {
        FieldtypeRepository::shouldReceive('find')->with('test')
            ->andReturn($fieldtype = new class extends Fieldtype
            {
                public function augment($value)
                {
                    return 'AUGMENTED '.strtoupper($value);
                }
            });

        $augmented = new class($this->blueprintThing) extends BaseAugmentedThing
        {
            public function foo()
            {
                return 'bar';
            }
        };

        tap($augmented->get('foo'), function ($value) {
            $this->assertInstanceOf(Value::class, $value);
            $this->assertEquals('bar', $value->raw());
            $this->assertEquals('bar', $value->value());
            $this->assertEquals('foo', $value->handle());
            $this->assertNull($value->fieldtype());
            $this->assertEquals($this->blueprintThing, $value->augmentable());
        });
    }

    #[Test]
    public function it_can_select_multiple_keys()
    {
        FieldtypeRepository::shouldReceive('find')->with('test')
            ->andReturn($fieldtype = new class extends Fieldtype
            {
                public function augment($value)
                {
                    return 'AUGMENTED '.strtoupper($value);
                }
            });

        $augmented = new class($this->blueprintThing) extends BaseAugmentedThing
        {
            public function keys()
            {
                return ['foo', 'slug', 'the_slug', 'hello', 'supplemented'];
            }

            public function hello()
            {
                return 'world';
            }
        };

        $result = $augmented->all();
        $this->assertInstanceOf(AugmentedCollection::class, $result);
        $this->assertEquals([
            'foo' => $foo = new Value('bar', 'foo', $fieldtype, $this->blueprintThing),
            'slug' => $slug = new Value('the-thing', 'slug', $fieldtype, $this->blueprintThing),
            'the_slug' => $theSlug = new Value('the-thing', 'the_slug', null, $this->blueprintThing),
            'hello' => $hello = new Value('world', 'hello', null, $this->blueprintThing),
            'supplemented' => $supplemented = new Value('supplemented value', 'supplemented', null, $this->blueprintThing),
        ], $result->all());

        $result = $augmented->select(['foo', 'hello']);
        $this->assertInstanceOf(AugmentedCollection::class, $result);
        $this->assertEveryItemIsInstanceOf(Value::class, $result);
        $this->assertEquals([
            'foo' => $foo,
            'hello' => $hello,
        ], $result->all());

        $this->assertEquals([
            'foo' => $foo,
        ], $augmented->select('foo')->all());

        $result = $augmented->except(['slug', 'hello']);
        $this->assertInstanceOf(AugmentedCollection::class, $result);
        $this->assertEquals([
            'foo' => $foo,
            'the_slug' => $theSlug,
            'supplemented' => $supplemented,
        ], $result->all());

        $this->assertEquals([
            'foo' => $foo,
            'slug' => $slug,
            'the_slug' => $theSlug,
            'supplemented' => $supplemented,
        ], $augmented->except('hello')->all());
    }

    #[Test]
    public function no_infinite_loop_when_getting_keys_that_match_methods()
    {
        $thing = new Thing([
            'select' => 'selected',
            'except' => 'excepted',
        ]);

        $augmented = new BaseAugmentedThing($thing);

        $this->assertEqualsValue('selected', $augmented->get('select'));
        $this->assertEqualsValue('excepted', $augmented->get('except'));
    }
}

class Thing
{
    use ContainsData;

    public function __construct($data)
    {
        $this->data = $data;
        $this->supplements = [
            'supplemented' => 'supplemented value',
        ];
    }

    public function slug()
    {
        return 'the-thing';
    }

    public function theSlug()
    {
        return $this->slug();
    }

    public function cantCallMe()
    {
        return 'nope';
    }
}

class BlueprintThing extends Thing
{
    public function blueprint()
    {
        return Blueprint::make()->setContents([
            'fields' => [
                [
                    'handle' => 'foo',
                    'field' => ['type' => 'test'],
                ],
                [
                    'handle' => 'slug',
                    'field' => ['type' => 'test'],
                ],
                [
                    'handle' => 'unused',
                    'field' => ['type' => 'test'],
                ],
            ],
        ]);
    }
}

class BaseAugmentedThing extends AbstractAugmented
{
    public function keys()
    {
        return array_keys(array_merge(
            $this->data->data(),
            $this->blueprintFields()->all()
        ));
    }
}
