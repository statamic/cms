<?php

namespace Tests\Data;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Data\AugmentedCollection;
use Statamic\Data\HasAugmentedData;
use Statamic\Fields\Value;

class AugmentedCollectionTest extends TestCase
{
    public function tearDown(): void
    {
        m::close();
    }

    /** @test */
    function it_calls_toArray_on_each_item()
    {
        $item1 = m::mock(Arrayable::class);
        $item1->shouldReceive('toArray')->once()->andReturn('foo.array');
        $item2 = m::mock(Arrayable::class);
        $item2->shouldReceive('toArray')->once()->andReturn('bar.array');
        $c = new AugmentedCollection([$item1, $item2]);
        $results = $c->toArray();

        $this->assertEquals(['foo.array', 'bar.array'], $results);
    }

    /** @test */
    function values_get_flagged_shallow_when_calling_toArray_with_flag()
    {
        $value = m::mock(Value::class);
        // $value->shouldNotReceive('toArray');
        $value->shouldReceive('shallow')->once()->andReturnSelf();
        $c = new AugmentedCollection([$value]);
        $results = $c->withShallowNesting()->toArray();

        $this->assertEquals([$value], $results);
    }

    /** @test */
    function values_do_not_get_flagged_shallow_when_calling_toArray_without_flag()
    {
        $value = m::mock(Value::class);
        $value->shouldNotReceive('toArray');
        $value->shouldNotReceive('shallow');
        $c = new AugmentedCollection([$value]);
        $results = $c->toArray();

        $this->assertEquals([$value], $results);
    }

    /** @test */
    public function it_json_serializes()
    {
        $value = m::mock(Value::class);
        $value->shouldReceive('jsonSerialize')->once()->andReturn('value json serialized');

        $c = new AugmentedCollection([
            new TestArrayableObject,
            new TestJsonableObject,
            new TestJsonSerializeObject,
            $augmentable = new TestAugmentableObject,
            'baz',
            $value,
        ]);

        $this->assertSame([
            ['foo' => 'bar'],
            ['foo' => 'bar'],
            ['foo' => 'bar'],
            $augmentable,
            'baz',
            'value json serialized',
        ], $c->jsonSerialize());
    }

    /** @test */
    function augmentables_get_shallow_augmented_when_json_serializing_with_flag()
    {
        $value = m::mock(Value::class);
        $value->shouldReceive('jsonSerialize')->once()->andReturn('value json serialized');

        $c = new AugmentedCollection([
            new TestArrayableObject,
            new TestJsonableObject,
            new TestJsonSerializeObject,
            new TestAugmentableObject,
            'baz',
            $value,
        ]);

        $this->assertSame([
            ['foo' => 'bar'],
            ['foo' => 'bar'],
            ['foo' => 'bar'],
            ['shallow augmented augmentable'],
            'baz',
            'value json serialized',
        ], $c->withShallowNesting()->jsonSerialize());
    }
}

class TestArrayableObject implements Arrayable
{
    public function toArray()
    {
        return ['foo' => 'bar'];
    }
}

class TestJsonableObject implements Jsonable
{
    public function toJson($options = 0)
    {
        return '{"foo":"bar"}';
    }
}

class TestJsonSerializeObject implements JsonSerializable
{
    public function jsonSerialize()
    {
        return ['foo' => 'bar'];
    }
}

class TestAugmentableObject implements Augmentable
{
    use HasAugmentedData;

    public function toShallowAugmentedArray()
    {
        return ['shallow augmented augmentable'];
    }
}
