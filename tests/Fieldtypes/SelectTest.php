<?php

namespace Tests\Fieldtypes;

use Statamic\Fields\Field;
use Statamic\Fields\LabeledValue;
use Statamic\Fieldtypes\Select;
use Tests\TestCase;

class SelectTest extends TestCase
{
    /** @test */
    function it_augments_to_a_LabeledValue_object_with_options_with_keys()
    {
        $field = (new Select)->setField(new Field('test', [
            'type' => 'select',
            'options' => [
                'au' => 'Australia',
                'ca' => 'Canada',
                'us' => 'USA',
            ]
        ]));

        $augmented = $field->augment('au');
        $this->assertInstanceOf(LabeledValue::class, $augmented);
        $this->assertEquals('au', $augmented->value());
        $this->assertEquals('Australia', $augmented->label());
    }

    /** @test */
    function it_augments_to_a_LabeledValue_object_with_options_without_keys()
    {
        $field = (new Select)->setField(new Field('test', [
            'type' => 'select',
            'options' => [
                'Australia',
                'Canada',
                'USA',
            ]
        ]));

        $augmented = $field->augment('Australia');
        $this->assertInstanceOf(LabeledValue::class, $augmented);
        $this->assertEquals('Australia', $augmented->value());
        $this->assertEquals('Australia', $augmented->label());
    }

    /** @test */
    function it_augments_to_a_LabeledValue_object_with_a_null_value()
    {
        $field = (new Select)->setField(new Field('test', [
            'type' => 'select',
            'options' => [
                'au' => 'Australia',
                'ca' => 'Canada',
                'us' => 'USA',
            ]
        ]));

        $augmented = $field->augment(null);
        $this->assertInstanceOf(LabeledValue::class, $augmented);
        $this->assertNull($augmented->value());
        $this->assertNull($augmented->label());
    }
}
