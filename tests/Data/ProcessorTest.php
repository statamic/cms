<?php

namespace Tests\Data;

use Mockery;
use Tests\TestCase;
use Statamic\Fields\Fieldset;
use Statamic\Data\Processor;

class ProcessorTest extends TestCase
{
    /** @test */
    function it_pre_processes_fields_in_the_fieldset_and_leaves_other_fields_alone()
    {
        $fieldset = tap(Mockery::mock(Fieldset::class), function ($fieldset) {
            $fieldset->shouldReceive('fieldtypes')->andReturn([
                $this->fieldtypeWithConfig(['name' => 'foo'])
            ]);
        });

        $processed = (new Processor($fieldset))->preProcess([
            'foo' => 'bar',
            'baz' => 'qux',
        ]);

        $expected = [
            'foo' => 'bar pre processed',
            'baz' => 'qux',
        ];

        $this->assertEquals($expected, $processed);
    }

    /** @test */
    function it_processes_fields_in_the_fieldset_and_leaves_other_fields_alone()
    {
        $fieldset = tap(Mockery::mock(Fieldset::class), function ($fieldset) {
            $fieldset->shouldReceive('fieldtypes')->andReturn([
                $this->fieldtypeWithConfig(['name' => 'foo'])
            ]);
        });

        $processed = (new Processor($fieldset))->process([
            'foo' => 'bar',
            'baz' => 'qux',
        ]);

        $expected = [
            'foo' => 'bar post processed',
            'baz' => 'qux',
        ];

        $this->assertEquals($expected, $processed);
    }

    /** @test */
    function it_adds_blank_values()
    {
        $fieldset = tap(Mockery::mock(Fieldset::class), function ($fieldset) {
            $fieldset->shouldReceive('fieldtypes')->andReturn([
                $this->fieldtypeWithConfig(['name' => 'one']),
                $this->fieldtypeWithConfig(['name' => 'two', 'default' => 'default from fieldset']),
                $this->fieldtypeWithConfig(['name' => 'three', 'default' => 'default from fieldset']),
                $this->fieldtypeWithConfig(['name' => 'four']),
            ]);
        });

        $processed = (new Processor($fieldset))->addBlankValues([
            'three' => 'foo', // exists in fieldset, but has a value in the data.
            'four' => 'bar',  // exists in fieldset, but has a value in the data.
            'five' => 'baz'   // doesn't exist in fieldset.
        ]);

        $expected = [
            'one' => 'blank value from fieldtype pre processed', // no value in data, no default in fieldset, so falls back to fieldtype
            'two' => 'default from fieldset pre processed',      // no value in data, but default is in fieldset
            'three' => 'foo',                                    // value is in data, so it overrides the default value.
            'four' => 'bar',                                     // value is in data, so it overrides the default (which would have come from fieldtype)
            'five' => 'baz',                                     // value is in data, doesn't exist in fieldset, so it's just left alone.
        ];

        $this->assertEquals($expected, $processed);
    }

    /** @test */
    function it_preprocesses_and_adds_blank_values()
    {

        $fieldset = tap(Mockery::mock(Fieldset::class), function ($fieldset) {
            $fieldset->shouldReceive('fieldtypes')->andReturn([
                $this->fieldtypeWithConfig(['name' => 'one']),
                $this->fieldtypeWithConfig(['name' => 'two', 'default' => 'default from fieldset']),
                $this->fieldtypeWithConfig(['name' => 'three', 'default' => 'default from fieldset']),
                $this->fieldtypeWithConfig(['name' => 'four']),
            ]);
        });

        $processed = (new Processor($fieldset))->preProcessWithBlanks([
            'three' => 'foo', // exists in fieldset, but has a value in the data.
            'four' => 'bar',  // exists in fieldset, but has a value in the data.
            'five' => 'baz'   // doesn't exist in fieldset.
        ]);

        $expected = [
            'one' => 'blank value from fieldtype pre processed', // no value in data, no default in fieldset, so falls back to fieldtype
            'two' => 'default from fieldset pre processed',      // no value in data, but default is in fieldset
            'three' => 'foo pre processed',                      // value is in data, so it overrides the default value.
            'four' => 'bar pre processed',                       // value is in data, so it overrides the default (which would have come from fieldtype)
            'five' => 'baz',                                     // value is in data, doesn't exist in fieldset, so it's just left alone.
        ];

        $this->assertEquals($expected, $processed);
    }

    /** @test */
    function removes_null_values_but_leaves_false_values()
    {
        $fieldset = tap(Mockery::mock(Fieldset::class), function ($fieldset) {
            $fieldset->shouldReceive('fieldtypes');
        });

        $processed = (new Processor($fieldset))->removeNullValues([
            'text' => 'foo',
            'array' => ['one', 'two'],
            'zero' => 0,
            'false' => false,
            'null' => null,
            'empty_string' => '',
            'empty_array' => [],
        ]);

        $expected = [
            'text' => 'foo',
            'array' => ['one', 'two'],
            'zero' => 0,
            'false' => false,
        ];

        $this->assertEquals($expected, $processed);
    }

    private function fieldtypeWithConfig($config)
    {
        return tap(new TestFieldtype, function ($fieldtype) use ($config) {
            $fieldtype->setFieldConfig($config);
        });
    }
}

class TestFieldtype extends \Statamic\Extend\Fieldtype
{
    public function preProcess($data)
    {
        return $data . ' pre processed';
    }

    public function process($data)
    {
        return $data . ' post processed';
    }

    public function blank()
    {
        return 'blank value from fieldtype';
    }
}