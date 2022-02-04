<?php

namespace Tests\Fields;

use Exception;
use Illuminate\Support\Collection;
use Mockery;
use Statamic\Fields\Fieldtype;
use Statamic\Fields\Value;
use Statamic\Fields\Values;
use Tests\TestCase;

class ValuesTest extends TestCase
{
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

    /**
     * @test
     * @dataProvider queryBuilderProvider
     **/
    public function query_builder_is_not_converted($builder)
    {
        $values = new Values($builder);

        $this->assertSame($builder, $values->getProxiedInstance());
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
     */
    public function completes_a_query($builder)
    {
        $builder->shouldReceive('get')->once()->andReturn(collect([
            'alfa' => 'bravo',
            'charlie' => 'delta',
        ]));

        $values = new Values($builder);

        $this->assertEquals('bravo', $values['alfa']);
        $this->assertEquals('delta', $values['charlie']);
        $this->assertEquals('bravo', $values->alfa);
        $this->assertEquals('delta', $values->charlie);
    }
}
