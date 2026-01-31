<?php

namespace Tests\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\Range;
use Tests\TestCase;

class RangeFieldtypeTest extends TestCase
{
    #[Test]
    public function it_processes_as_integer_with_integer_config()
    {
        $fieldtype = (new Range())->setField(new Field('test', [
            'type' => 'range',
            'min' => 0,
            'max' => 100,
            'step' => 1,
        ]));

        $result = $fieldtype->process('7');

        $this->assertIsInt($result);
        $this->assertEquals(7, $result);
    }

    #[Test]
    public function it_processes_as_float_with_decimal_step()
    {
        $fieldtype = (new Range())->setField(new Field('test', [
            'type' => 'range',
            'min' => 0,
            'max' => 100,
            'step' => 0.1,
        ]));

        $result = $fieldtype->process('7.5');

        $this->assertIsFloat($result);
        $this->assertEquals(7.5, $result);
    }

    #[Test]
    public function it_processes_as_float_with_decimal_min()
    {
        $fieldtype = (new Range())->setField(new Field('test', [
            'type' => 'range',
            'min' => 0.5,
            'max' => 100,
            'step' => 1,
        ]));

        $result = $fieldtype->process('7');

        $this->assertIsFloat($result);
        $this->assertEquals(7.0, $result);
    }

    #[Test]
    public function it_processes_as_float_with_decimal_max()
    {
        $fieldtype = (new Range())->setField(new Field('test', [
            'type' => 'range',
            'min' => 0,
            'max' => 99.9,
            'step' => 1,
        ]));

        $result = $fieldtype->process('7');

        $this->assertIsFloat($result);
        $this->assertEquals(7.0, $result);
    }

    #[Test]
    public function it_processes_zero_as_integer_with_integer_config()
    {
        $fieldtype = (new Range())->setField(new Field('test', [
            'type' => 'range',
            'min' => 0,
            'max' => 100,
            'step' => 1,
        ]));

        $result = $fieldtype->process('0');

        $this->assertIsInt($result);
        $this->assertEquals(0, $result);
    }

    #[Test]
    public function it_processes_negative_values_with_decimal_step()
    {
        $fieldtype = (new Range())->setField(new Field('test', [
            'type' => 'range',
            'min' => -10,
            'max' => 10,
            'step' => 0.5,
        ]));

        $result = $fieldtype->process('-2.5');

        $this->assertIsFloat($result);
        $this->assertEquals(-2.5, $result);
    }

    #[Test]
    public function it_returns_int_graphql_type_with_integer_config()
    {
        $fieldtype = (new Range())->setField(new Field('test', [
            'type' => 'range',
            'min' => 0,
            'max' => 100,
            'step' => 1,
        ]));

        $type = $fieldtype->toGqlType();

        $this->assertEquals('Int', $type->name);
    }

    #[Test]
    public function it_returns_float_graphql_type_with_decimal_config()
    {
        $fieldtype = (new Range())->setField(new Field('test', [
            'type' => 'range',
            'min' => 0,
            'max' => 100,
            'step' => 0.1,
        ]));

        $type = $fieldtype->toGqlType();

        $this->assertEquals('Float', $type->name);
    }
}
