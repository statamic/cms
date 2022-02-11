<?php

namespace Tests\Fields;

use BadMethodCallException;
use Exception;
use Facades\Tests\Factories\EntryFactory;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use Mockery;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Data\HasAugmentedData;
use Statamic\Entries\EntryCollection;
use Statamic\Fields\Fieldtype;
use Statamic\Fields\Value;
use Statamic\Fields\Values;
use Statamic\Fields\ValuesCollection;
use Statamic\Fields\ValuesQueryBuilder;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ValuesTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        $this->fieldtype = new class extends Fieldtype
        {
            protected static $handle = 'test';

            public function augment($value)
            {
                return $value.' (augmented)';
            }
        };

        $this->fieldtype->register();
    }

    /** @test */
    public function array_is_converted_to_a_collection()
    {
        $values = new Values(['foo' => 'bar']);

        $this->assertInstanceOf(Collection::class, $collection = $values->getProxiedInstance());
        $this->assertEquals(['foo' => 'bar'], $collection->all());
    }

    /** @test */
    public function collection_is_not_converted()
    {
        $values = new Values(collect(['foo' => 'bar']));

        $this->assertInstanceOf(Collection::class, $collection = $values->getProxiedInstance());
        $this->assertEquals(['foo' => 'bar'], $collection->all());
    }

    /** @test */
    public function its_arrayable()
    {
        $mockOne = Mockery::mock(Collection::class)->shouldReceive('toArray')->andReturn(['title' => 'first'])->getMock();
        $mockTwo = Mockery::mock(Collection::class)->shouldReceive('toArray')->andReturn(['title' => 'second'])->getMock();

        $values = new Values(collect([
            'one' => $mockOne,
            'two' => $mockTwo,
        ]));

        $this->assertInstanceOf(Arrayable::class, $values);

        $this->assertEquals([
            'one' => ['title' => 'first'],
            'two' => ['title' => 'second'],
        ], $values->toArray());
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
    public function array_access()
    {
        $values = new Values([
            'alfa' => 'bravo',
            'charlie' => new Value('delta', null, $this->fieldtype),
        ]);

        $this->assertTrue(isset($values['alfa']));
        $this->assertEquals('bravo', $values['alfa']);
        $this->assertTrue(isset($values['charlie']));
        $this->assertEquals('delta (augmented)', $values['charlie']);
        $this->assertIsString($values['charlie']);
        $this->assertNull($values['missing']);
    }

    /** @test */
    public function setting_by_array_access_is_not_supported()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Cannot set values by array access.');

        $values = new Values([
            'alfa' => 'bravo',
            'charlie' => new Value('delta', null, $this->fieldtype),
        ]);

        $values['echo'] = 'foxtrot';
    }

    /** @test */
    public function unsetting_by_array_access_is_not_supported()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Cannot unset values by array access.');

        $values = new Values([
            'alfa' => 'bravo',
            'charlie' => new Value('delta', null, $this->fieldtype),
        ]);

        unset($values['charlie']);
    }

    /** @test */
    public function property_access()
    {
        $values = new Values([
            'alfa' => 'bravo',
            'charlie' => new Value('delta', null, $this->fieldtype),
        ]);

        $this->assertEquals('bravo', $values->alfa);
        $this->assertEquals('delta (augmented)', $values->charlie);
        $this->assertIsString($values->charlie);
        $this->assertNull($values->missing);
    }

    /** @test */
    public function setting_by_property_access_is_not_supported()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Cannot set values by property access.');

        $values = new Values([
            'alfa' => 'bravo',
            'charlie' => new Value('delta', null, $this->fieldtype),
        ]);

        $values->echo = 'foxtrot';
    }

    /** @test */
    public function raw_values()
    {
        $values = new Values([
            'alfa' => 'bravo',
            'charlie' => $value = new Value('delta', null, $this->fieldtype),
        ]);

        $this->assertEquals('bravo', $values->raw('alfa'));
        $this->assertEquals('delta', $values->raw('charlie'));
        $this->assertIsString($values->raw('charlie'));
        $this->assertNull($values->raw('missing'));
    }

    /**
     * @test
     * @dataProvider queryBuilderProvider
     **/
    public function it_gets_a_query($builder)
    {
        $values = new Values(['the_query_field' => $builder]);

        $this->assertInstanceOf(ValuesQueryBuilder::class, $values->the_query_field());
    }

    /** @test */
    public function it_throws_exception_if_trying_to_get_query_for_field_that_isnt_a_query()
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Method Statamic\Fields\Values::not_a_query does not exist.');

        $values = new Values(['not_a_query' => 'test']);

        $this->assertInstanceOf(ValuesCollection::class, $values->not_a_query());
    }

    /** @test */
    public function it_throws_exception_if_trying_to_get_query_for_missing_field()
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Method Statamic\Fields\Values::missing does not exist.');

        $values = new Values(['not_a_query' => 'test']);

        $this->assertInstanceOf(ValuesCollection::class, $values->missing());
    }

    /**
     * @test
     * @dataProvider queryBuilderProvider
     */
    public function completes_a_query_that_ends_up_with_results($builder)
    {
        $builder->shouldReceive('get')->once()->andReturn(EntryCollection::make([
            EntryFactory::collection('test')->slug('a')->data(['foo' => 'alfa', 'bar' => 'bravo'])->create(),
            EntryFactory::collection('test')->slug('b')->data(['foo' => 'charlie', 'bar' => 'delta'])->create(),
        ]));

        $fieldtype = new FakeFieldtypeThatAugmentsToMockedBuilder($builder);

        $values = new Values([
            'related_entries' => new Value('irrelevant, mocking the augment method', null, $fieldtype),
        ]);

        $this->assertInstanceOf(ValuesCollection::class, $values->related_entries);
        $this->assertInstanceOf(Collection::class, $values->related_entries->getProxiedInstance());
        $this->assertEveryItemIsInstanceOf(Values::class, $values->related_entries->getProxiedInstance());

        $this->assertInstanceOf(ValuesCollection::class, $values['related_entries']);
        $this->assertInstanceOf(Collection::class, $values['related_entries']->getProxiedInstance());
        $this->assertEveryItemIsInstanceOf(Values::class, $values['related_entries']->getProxiedInstance());

        $this->assertInstanceOf(ValuesQueryBuilder::class, $values->related_entries());
        $this->assertSame($builder, $values->related_entries()->getProxiedInstance());

        $this->assertFalse($values->related_entries->isEmpty());
        $this->assertCount(2, $values->related_entries);

        $echos = [];
        foreach ($values->related_entries as $entry) {
            $this->assertInstanceOf(Values::class, $entry);
            $echos[] = $entry->slug;
            $echos[] = $entry->foo;
            $echos[] = $entry->bar;
        }
        $this->assertSame(['a', 'alfa', 'bravo', 'b', 'charlie', 'delta'], $echos);
    }

    /**
     * @test
     * @dataProvider queryBuilderProvider
     */
    public function completes_a_query_that_ends_up_with_no_results($builder)
    {
        $builder->shouldReceive('get')->once()->andReturn(EntryCollection::make());

        $fieldtype = new FakeFieldtypeThatAugmentsToMockedBuilder($builder);

        $values = new Values([
            'related_entries' => new Value('irrelevant, mocking the augment method', null, $fieldtype),
        ]);

        $this->assertInstanceOf(ValuesCollection::class, $values->related_entries);
        $this->assertInstanceOf(Collection::class, $values->related_entries->getProxiedInstance());
        $this->assertEveryItemIsInstanceOf(Values::class, $values->related_entries->getProxiedInstance());

        $this->assertInstanceOf(ValuesCollection::class, $values['related_entries']);
        $this->assertInstanceOf(Collection::class, $values['related_entries']->getProxiedInstance());
        $this->assertEveryItemIsInstanceOf(Values::class, $values['related_entries']->getProxiedInstance());

        $this->assertInstanceOf(ValuesQueryBuilder::class, $values->related_entries());
        $this->assertSame($builder, $values->related_entries()->getProxiedInstance());

        $this->assertTrue($values->related_entries->isEmpty());
        $this->assertCount(0, $values->related_entries);
    }

    /**
     * @test
     * @dataProvider queryBuilderProvider
     **/
    public function it_calls_query_builder_methods($builder)
    {
        $builder->shouldReceive('get')->once()->andReturn(EntryCollection::make([
            EntryFactory::collection('test')->slug('a')->data(['foo' => 'alfa', 'bar' => 'bravo'])->create(),
            EntryFactory::collection('test')->slug('b')->data(['foo' => 'charlie', 'bar' => 'delta'])->create(),
        ]));

        $builder->shouldReceive('foo')->with(1, 2)->andReturnSelf();
        $builder->shouldReceive('bar')->with(3, 4)->andReturnSelf();

        $fieldtype = new FakeFieldtypeThatAugmentsToMockedBuilder($builder);

        $values = new Values([
            'related_entries' => new Value(['id1', 'id2'], null, $fieldtype),
        ]);

        $valuesQueryBuilder = $values->related_entries()->foo(1, 2)->bar(3, 4);

        $this->assertInstanceOf(ValuesQueryBuilder::class, $valuesQueryBuilder);
        $this->assertInstanceOf(ValuesCollection::class, $valuesCollection = $valuesQueryBuilder->get());
        $this->assertInstanceOf(Collection::class, $valuesCollection->getProxiedInstance());
        $this->assertEveryItemIsInstanceOf(Values::class, $valuesCollection->getProxiedInstance());

        $echos = [];
        foreach ($valuesCollection as $entry) {
            $this->assertInstanceOf(Values::class, $entry);
            $echos[] = $entry->slug;
            $echos[] = $entry->foo;
            $echos[] = $entry->bar;
        }
        $this->assertSame(['a', 'alfa', 'bravo', 'b', 'charlie', 'delta'], $echos);
    }

    /** @test */
    public function its_iterable()
    {
        $values = new Values(['foo' => 'bar', 'baz' => 'qux']);

        $results = [];
        foreach ($values as $key => $value) {
            $results[] = $key;
            $results[] = $value;
        }
        $this->assertEquals(['foo', 'bar', 'baz', 'qux'], $results);
    }
}

class FakeFieldtypeThatAugmentsToMockedBuilder extends Fieldtype
{
    protected static $handle = 'test';

    public function __construct($builder)
    {
        $this->builder = $builder;
    }

    public function augment($ids)
    {
        return $this->builder;
    }
}

class TestAugmentableObject implements Augmentable
{
    use HasAugmentedData;
    protected $data = [];

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function augmentedArrayData()
    {
        return $this->data;
    }
}
