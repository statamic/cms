<?php

namespace Tests\Data;

use Facades\Statamic\Fields\FieldtypeRepository;
use Statamic\Data\AbstractAugmented;
use Statamic\Data\AugmentedCollection;
use Statamic\Data\ContainsData;
use Statamic\Facades\Blueprint;
use Statamic\Fields\Fieldtype;
use Statamic\Fields\Value;
use Tests\TestCase;

class AugmentedTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->thing = new Thing($data = [
            'foo' => 'bar',
            'baz' => 'qux',
        ]);

        $this->blueprintThing = new BlueprintThing($data);
    }

    /** @test */
    public function it_gets_a_single_value_by_key()
    {
        $augmented = new class($this->thing) extends BaseAugmentedThing
        {
            //
        };

        $this->assertEquals('bar', $augmented->get('foo'));
        $this->assertNull($augmented->get('unknown'));
    }

    /** @test */
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

        $this->assertEquals('bar (value)', $augmented->get('foo'));
        $this->assertNull($augmented->get('unknown'));
    }

    /** @test */
    public function it_gets_a_value_from_the_thing_if_theres_a_corresponding_method_for_a_key()
    {
        $augmented = new class($this->thing) extends BaseAugmentedThing
        {
            public function keys()
            {
                return ['slug', 'the_slug'];
            }
        };

        $this->assertEquals('the-thing', $augmented->get('slug'));
        $this->assertEquals('the-thing', $augmented->get('the_slug'));
        $this->assertEquals('the-thing', $augmented->get('theSlug'));
        $this->assertNull($augmented->get('cant_call_me'));
        $this->assertNull($augmented->get('cantCallMe'));
    }

    /** @test */
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

        $this->assertEquals('the-augmented-thing', $augmented->get('slug'));
        $this->assertEquals('the-augmented-thing', $augmented->get('the_slug'));
        $this->assertEquals('the-augmented-thing', $augmented->get('theSlug'));
    }

    /** @test */
    public function a_value_object_is_returned_if_the_thing_has_a_blueprint_and_theres_a_matching_field()
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

        $this->assertEquals('qux', $augmented->get('baz'));
        $this->assertEquals('the-thing', $augmented->get('the_slug'));
        $this->assertEquals('world', $augmented->get('hello'));
        $this->assertNull($augmented->get('unknown'));
    }

    /** @test */
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
            'the_slug' => 'the-thing',
            'hello' => 'world',
            'supplemented' => 'supplemented value',
        ], $result->all());

        $result = $augmented->select(['foo', 'hello']);
        $this->assertInstanceOf(AugmentedCollection::class, $result);
        $this->assertEquals([
            'foo' => $foo,
            'hello' => 'world',
        ], $result->all());

        $this->assertEquals([
            'foo' => $foo,
        ], $augmented->select('foo')->all());

        $result = $augmented->except(['slug', 'hello']);
        $this->assertInstanceOf(AugmentedCollection::class, $result);
        $this->assertEquals([
            'foo' => $foo,
            'the_slug' => 'the-thing',
            'supplemented' => 'supplemented value',
        ], $result->all());

        $this->assertEquals([
            'foo' => $foo,
            'slug' => $slug,
            'the_slug' => 'the-thing',
            'supplemented' => 'supplemented value',
        ], $augmented->except('hello')->all());
    }

    /** @test */
    public function no_infinite_loop_when_getting_keys_that_match_methods()
    {
        $thing = new Thing([
            'select' => 'selected',
            'except' => 'excepted',
        ]);

        $augmented = new BaseAugmentedThing($thing);

        $this->assertEquals('selected', $augmented->get('select'));
        $this->assertEquals('excepted', $augmented->get('except'));
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
