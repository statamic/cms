<?php

namespace Tests;

use Facades\Statamic\Fields\FieldtypeRepository;
use Statamic\Data\Augmented;
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
    function it_gets_a_single_value_by_key()
    {
        $augmented = new class($this->thing) extends BaseAugmentedThing {
            //
        };

        $this->assertEquals('bar', $augmented->get('foo'));
        $this->assertNull($augmented->get('unknown'));
    }

    /** @test */
    function it_gets_a_value_from_the_thing_if_theres_a_method()
    {
        $augmented = new class($this->thing) extends BaseAugmentedThing {
            //
        };

        $this->assertEquals('the-thing', $augmented->get('slug'));
        $this->assertEquals('the-thing', $augmented->get('the_slug'));
        $this->assertEquals('the-thing', $augmented->get('theSlug'));
    }

    /** @test */
    function it_gets_a_value_from_the_augmented_thing_if_theres_a_method()
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
    function a_value_object_is_returned_if_the_thing_has_a_blueprint_and_theres_a_matching_field()
    {
        FieldtypeRepository::shouldReceive('find')->with('test')
            ->andReturn($fieldtype = new class extends Fieldtype
            {
                public function augment($value)
                {
                    return 'AUGMENTED ' . strtoupper($value);
                }
            });

        $augmented = new class($this->blueprintThing) extends BaseAugmentedThing {
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
    function it_can_select_multiple_keys()
    {
        FieldtypeRepository::shouldReceive('find')->with('test')
            ->andReturn($fieldtype = new class extends Fieldtype
            {
                public function augment($value)
                {
                    return 'AUGMENTED ' . strtoupper($value);
                }
            });

        $augmented = new class($this->blueprintThing) extends BaseAugmentedThing {
            public function keys()
            {
                return ['foo', 'slug', 'the_slug', 'hello'];
            }

            public function hello()
            {
                return 'world';
            }
        };

        $this->assertEquals([
            'foo' => $foo = new Value('bar', 'foo', $fieldtype, $this->blueprintThing),
            'slug' => $slug = new Value('the-thing', 'slug', $fieldtype, $this->blueprintThing),
            'the_slug' => 'the-thing',
            'hello' => 'world',
            'unused' => $unused = new Value(null, 'unused', $fieldtype, $this->blueprintThing),
        ], $augmented->all());

        $this->assertEquals([
            'foo' => $foo,
            'hello' => 'world',
        ], $augmented->select(['foo', 'hello']));

        $this->assertEquals([
            'foo' => $foo,
        ], $augmented->select('foo'));

        $this->assertEquals([
            'foo' => $foo,
            'the_slug' => 'the-thing',
            'unused' => $unused,
        ], $augmented->except(['slug', 'hello']));

        $this->assertEquals([
            'foo' => $foo,
            'slug' => $slug,
            'the_slug' => 'the-thing',
            'unused' => $unused,
        ], $augmented->except('hello'));
    }
}

class Thing
{
    use ContainsData;

    function __construct($data)
    {
        $this->data = $data;
    }

    public function slug()
    {
        return 'the-thing';
    }

    public function theSlug()
    {
        return $this->slug();
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
                ]
            ]
        ]);
    }
}

class BaseAugmentedThing extends Augmented
{
    public function keys()
    {
        return [];
    }
}
