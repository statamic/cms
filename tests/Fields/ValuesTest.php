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
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ValuesTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private $fieldtype;

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
    public function it_can_get_itself_as_an_array()
    {
        $values = new Values(collect(['foo' => 'bar']));

        $this->assertEquals(['foo' => 'bar'], $values->all());
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

        $this->assertSame($builder, $values->the_query_field());
    }

    /** @test */
    public function it_throws_exception_if_trying_to_get_query_for_field_that_isnt_a_query()
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Method Statamic\Fields\Values::not_a_query does not exist.');

        $values = new Values(['not_a_query' => 'test']);

        $values->not_a_query();
    }

    /** @test */
    public function it_throws_exception_if_trying_to_get_query_for_missing_field()
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Method Statamic\Fields\Values::missing does not exist.');

        $values = new Values(['not_a_query' => 'test']);

        $values->missing();
    }

    /**
     * @test
     * @dataProvider queryBuilderProvider
     */
    public function completes_a_query($builder)
    {
        $builder->shouldReceive('get')->andReturn($queryResults = EntryCollection::make([
            EntryFactory::collection('test')->slug('a')->data(['foo' => 'alfa', 'bar' => 'bravo'])->create(),
            EntryFactory::collection('test')->slug('b')->data(['foo' => 'charlie', 'bar' => 'delta'])->create(),
        ]));

        $fieldtype = new FakeFieldtypeThatAugmentsToMockedBuilder($builder);

        $values = new Values([
            'related_entries' => new Value('irrelevant, mocking the augment method', null, $fieldtype),
        ]);

        $this->assertSame($queryResults, $values->related_entries);
        $this->assertSame($queryResults, $values['related_entries']);
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

    /** @test */
    public function its_json_serializable()
    {
        $values = new Values(['foo' => 'bar', 'baz' => 'qux']);

        $this->assertEquals('{"foo":"bar","baz":"qux"}', json_encode($values));
    }
}

class FakeFieldtypeThatAugmentsToMockedBuilder extends Fieldtype
{
    protected static $handle = 'test';

    private $builder;

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
