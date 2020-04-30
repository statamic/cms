<?php

namespace Tests\Fieldtypes;

use Statamic\Fields\Field;
use Statamic\Fields\LabeledValue;
use Statamic\Fieldtypes\Radio;
use Tests\TestCase;

class RadioTest extends TestCase
{
    /** @test */
    public function it_augments_to_a_LabeledValue_object_with_options_with_keys()
    {
        $field = (new Radio)->setField(new Field('test', [
            'type' => 'radio',
            'options' => [
                'au' => 'Australia',
                'ca' => 'Canada',
                'us' => 'USA',
            ],
        ]));

        $augmented = $field->augment('au');
        $this->assertInstanceOf(LabeledValue::class, $augmented);
        $this->assertEquals('au', $augmented->value());
        $this->assertEquals('Australia', $augmented->label());
    }

    /** @test */
    public function it_augments_to_a_LabeledValue_object_with_options_without_keys()
    {
        $field = (new Radio)->setField(new Field('test', [
            'type' => 'radio',
            'options' => [
                'Australia',
                'Canada',
                'USA',
            ],
        ]));

        $augmented = $field->augment('Australia');
        $this->assertInstanceOf(LabeledValue::class, $augmented);
        $this->assertEquals('Australia', $augmented->value());
        $this->assertEquals('Australia', $augmented->label());
    }

    /** @test */
    public function it_augments_to_a_LabeledValue_object_with_a_null_value()
    {
        $field = (new Radio)->setField(new Field('test', [
            'type' => 'radio',
            'options' => [
                'au' => 'Australia',
                'ca' => 'Canada',
                'us' => 'USA',
            ],
        ]));

        $augmented = $field->augment(null);
        $this->assertInstanceOf(LabeledValue::class, $augmented);
        $this->assertNull($augmented->value());
        $this->assertNull($augmented->label());
    }
}
