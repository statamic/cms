<?php

namespace Tests\Data;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Database\Query\Builder as LaravelQueryBuilder;
use JsonSerializable;
use Mockery as m;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Contracts\Query\Builder as StatamicQueryBuilder;
use Statamic\Data\AugmentedCollection;
use Statamic\Data\HasAugmentedData;
use Statamic\Fields\Value;
use Tests\TestCase;

class AugmentedCollectionTest extends TestCase
{
    #[Test]
    public function it_calls_toArray_on_each_item()
    {
        $item1 = m::mock(Arrayable::class);
        $item1->shouldReceive('toArray')->once()->andReturn('foo.array');
        $item2 = m::mock(Arrayable::class);
        $item2->shouldReceive('toArray')->once()->andReturn('bar.array');
        $c = new AugmentedCollection([$item1, $item2]);
        $results = $c->toArray();

        $this->assertEquals(['foo.array', 'bar.array'], $results);
    }

    #[Test]
    public function values_get_flagged_shallow_when_calling_toArray_with_flag()
    {
        $value = m::mock(Value::class);
        // $value->shouldNotReceive('toArray');
        $value->shouldReceive('isRelationship')->andReturnFalse();
        $value->shouldReceive('shallow')->once()->andReturn($value);
        $value->shouldReceive('resolve')->once()->andReturnSelf();
        $c = new AugmentedCollection([$value]);
        $results = $c->withShallowNesting()->toArray();

        $this->assertEquals([$value], $results);
    }

    #[Test]
    public function values_do_not_get_flagged_shallow_when_calling_toArray_without_flag()
    {
        $value = m::mock(Value::class);
        $value->shouldReceive('isRelationship')->andReturnFalse();
        $value->shouldReceive('resolve')->once()->andReturnSelf();
        $value->shouldNotReceive('toArray');
        $value->shouldNotReceive('shallow');
        $c = new AugmentedCollection([$value]);
        $results = $c->toArray();

        $this->assertEquals([$value], $results);
    }

    #[Test]
    public function augmentables_get_converted_to_shallow_array_with_flag()
    {
        $augmentable = m::mock(Augmentable::class);
        $augmentable->shouldNotReceive('toArray');
        $augmentable->shouldReceive('toShallowAugmentedArray')->once()->andReturn(['augmented array']);
        $c = new AugmentedCollection([$augmentable]);
        $results = $c->withShallowNesting()->toArray();

        $this->assertEquals([['augmented array']], $results);
    }

    #[Test]
    public function it_converts_value_objects_to_their_augmented_values_with_flag()
    {
        $statamicQuery = m::mock(StatamicQueryBuilder::class);
        $statamicQuery->shouldReceive('get')->andReturn(collect(['statamic', 'query', 'builder', 'results']));
        $laravelQuery = m::mock(LaravelQueryBuilder::class);
        $laravelQuery->shouldReceive('get')->andReturn(collect(['laravel', 'query', 'builder', 'results']));

        $a = new Value('alfa');
        $b = new Value('bravo');
        $c = new Value([
            'charlie',
            new Value(collect([
                'delta',
                new Value([
                    'echo',
                    'foxtrot',
                ]),
            ])),
            new Value($statamicQuery),
            new Value($laravelQuery),
        ]);

        $c = new AugmentedCollection([$a, $b, $c, 'golf']);

        $results = $c->withEvaluation()->toArray();

        $this->assertEquals([
            'alfa',
            'bravo',
            [
                'charlie',
                [
                    'delta',
                    [
                        'echo',
                        'foxtrot',
                    ],
                ],
                ['statamic', 'query', 'builder', 'results'],
                ['laravel', 'query', 'builder', 'results'],
            ],
            'golf',
        ], $results);
    }

    #[Test]
    public function it_does_not_convert_value_objects_to_their_augmented_values_with_explicit_flag_or_without_any_flag()
    {
        $item1 = m::mock(Value::class);
        $item1->shouldReceive('value')->never();
        $item1->shouldReceive('isRelationship')->andReturnFalse();
        $item1->shouldReceive('resolve')->andReturnSelf();
        $item2 = m::mock(Value::class);
        $item2->shouldReceive('value')->never();
        $item2->shouldReceive('isRelationship')->andReturnFalse();
        $item2->shouldReceive('resolve')->andReturnSelf();

        $c = new AugmentedCollection([$item1, $item2, 'baz']);

        $results = $c->toArray();
        $this->assertEquals([$item1, $item2, 'baz'], $results);

        $results = $c->withoutEvaluation()->toArray();
        $this->assertEquals([$item1, $item2, 'baz'], $results);
    }

    #[Test]
    public function it_json_serializes()
    {
        $value = m::mock(Value::class);
        $value->shouldReceive('resolve')->once()->andReturnSelf();
        $value->shouldReceive('jsonSerialize')->once()->andReturn('value json serialized');

        $c = new AugmentedCollection([
            new TestArrayableObject,
            new TestJsonableObject,
            new TestJsonSerializeObject,
            $augmentable = new TestAugmentableObject(['foo' => 'bar']),
            'baz',
            $value,
        ]);

        $this->assertSame([
            ['foo' => 'bar'],
            ['foo' => 'bar'],
            ['foo' => 'bar'],
            ['foo' => 'bar'],
            'baz',
            'value json serialized',
        ], $c->jsonSerialize());
    }

    #[Test]
    public function augmentables_get_shallow_augmented_when_json_serializing_with_flag()
    {
        $value = m::mock(Value::class);
        $value->shouldReceive('resolve')->once()->andReturnSelf();
        $value->shouldReceive('jsonSerialize')->once()->andReturn('value json serialized');

        $c = new AugmentedCollection([
            new TestArrayableObject,
            new TestJsonableObject,
            new TestJsonSerializeObject,
            new TestAugmentableObject(['foo' => 'bar']),
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
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return ['foo' => 'bar'];
    }
}

class TestAugmentableObject implements Augmentable
{
    use HasAugmentedData;

    public function __construct(private $data)
    {

    }

    public function augmentedArrayData()
    {
        return $this->data;
    }

    public function toShallowAugmentedArray()
    {
        return ['shallow augmented augmentable'];
    }
}
