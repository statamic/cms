<?php

namespace Tests\Fieldtypes;

use Statamic\Fields\LabeledValue;

trait LabeledValueTests
{
    /** @test */
    public function it_augments_to_a_LabeledValue_object_with_options_with_keys()
    {
        $field = $this->field([
            'type' => 'select',
            'options' => [
                'au' => 'Australia',
                'ca' => 'Canada',
                'us' => 'USA',
            ],
        ]);

        $augmented = $field->augment('au');
        $this->assertInstanceOf(LabeledValue::class, $augmented);
        $this->assertEquals('au', $augmented->value());
        $this->assertEquals('Australia', $augmented->label());

        $augmented = $field->augment(null);
        $this->assertInstanceOf(LabeledValue::class, $augmented);
        $this->assertNull($augmented->value());
        $this->assertNull($augmented->label());

        $augmented = $field->augment(false);
        $this->assertInstanceOf(LabeledValue::class, $augmented);
        $this->assertFalse($augmented->value());
        $this->assertFalse($augmented->label());

        $augmented = $field->augment(true);
        $this->assertInstanceOf(LabeledValue::class, $augmented);
        $this->assertTrue($augmented->value());
        $this->assertTrue($augmented->label());

        $augmented = $field->augment('missing');
        $this->assertInstanceOf(LabeledValue::class, $augmented);
        $this->assertEquals('missing', $augmented->value());
        $this->assertEquals('missing', $augmented->label());
    }

    /** @test */
    public function it_augments_to_a_LabeledValue_object_with_options_without_keys()
    {
        $field = $this->field([
            'type' => 'select',
            'options' => [
                'Australia',
                'Canada',
                'USA',
            ],
        ]);

        $augmented = $field->augment('Australia');
        $this->assertInstanceOf(LabeledValue::class, $augmented);
        $this->assertEquals('Australia', $augmented->value());
        $this->assertEquals('Australia', $augmented->label());

        $augmented = $field->augment(null);
        $this->assertInstanceOf(LabeledValue::class, $augmented);
        $this->assertNull($augmented->value());
        $this->assertNull($augmented->label());

        $augmented = $field->augment(false);
        $this->assertInstanceOf(LabeledValue::class, $augmented);
        $this->assertFalse($augmented->value());
        $this->assertFalse($augmented->label());

        $augmented = $field->augment(true);
        $this->assertInstanceOf(LabeledValue::class, $augmented);
        $this->assertTrue($augmented->value());
        $this->assertTrue($augmented->label());

        $augmented = $field->augment('missing');
        $this->assertInstanceOf(LabeledValue::class, $augmented);
        $this->assertEquals('missing', $augmented->value());
        $this->assertEquals('missing', $augmented->label());
    }

    /** @test */
    public function it_augments_to_a_LabeledValue_object_with_a_null_value()
    {
        $field = $this->field([
            'type' => 'select',
            'options' => [
                'au' => 'Australia',
                'ca' => 'Canada',
                'us' => 'USA',
            ],
        ]);

        $augmented = $field->augment(null);
        $this->assertInstanceOf(LabeledValue::class, $augmented);
        $this->assertNull($augmented->value());
        $this->assertNull($augmented->label());

        $augmented = $field->augment(null);
        $this->assertInstanceOf(LabeledValue::class, $augmented);
        $this->assertNull($augmented->value());
        $this->assertNull($augmented->label());

        $augmented = $field->augment(false);
        $this->assertInstanceOf(LabeledValue::class, $augmented);
        $this->assertFalse($augmented->value());
        $this->assertFalse($augmented->label());

        $augmented = $field->augment(true);
        $this->assertInstanceOf(LabeledValue::class, $augmented);
        $this->assertTrue($augmented->value());
        $this->assertTrue($augmented->label());

        $augmented = $field->augment('missing');
        $this->assertInstanceOf(LabeledValue::class, $augmented);
        $this->assertEquals('missing', $augmented->value());
        $this->assertEquals('missing', $augmented->label());
    }

    /** @test */
    public function it_augments_to_a_LabeledValue_object_with_boolean_casting()
    {
        $field = $this->field([
            'type' => 'select',
            'cast_booleans' => true,
            'options' => [
                'true' => 'Yup',
                'false' => 'Nope',
            ],
        ]);

        $augmented = $field->augment(null);
        $this->assertInstanceOf(LabeledValue::class, $augmented);
        $this->assertNull($augmented->value());
        $this->assertNull($augmented->label());

        $augmented = $field->augment(false);
        $this->assertInstanceOf(LabeledValue::class, $augmented);
        $this->assertFalse($augmented->value());
        $this->assertEquals('Nope', $augmented->label());

        $augmented = $field->augment(true);
        $this->assertInstanceOf(LabeledValue::class, $augmented);
        $this->assertTrue($augmented->value());
        $this->assertEquals('Yup', $augmented->label());

        $augmented = $field->augment('missing');
        $this->assertInstanceOf(LabeledValue::class, $augmented);
        $this->assertEquals('missing', $augmented->value());
        $this->assertEquals('missing', $augmented->label());
    }

    /** @test */
    public function it_augments_to_a_LabeledValue_object_with_boolean_casting_and_a_null_option()
    {
        $field = $this->field([
            'type' => 'select',
            'cast_booleans' => true,
            'options' => [
                'true' => 'Yup',
                'false' => 'Nope',
                'null' => 'Dunno',
            ],
        ]);

        $augmented = $field->augment(null);
        $this->assertInstanceOf(LabeledValue::class, $augmented);
        $this->assertNull($augmented->value());
        $this->assertEquals('Dunno', $augmented->label());

        $augmented = $field->augment(false);
        $this->assertInstanceOf(LabeledValue::class, $augmented);
        $this->assertFalse($augmented->value());
        $this->assertEquals('Nope', $augmented->label());

        $augmented = $field->augment(true);
        $this->assertInstanceOf(LabeledValue::class, $augmented);
        $this->assertTrue($augmented->value());
        $this->assertEquals('Yup', $augmented->label());

        $augmented = $field->augment('missing');
        $this->assertInstanceOf(LabeledValue::class, $augmented);
        $this->assertEquals('missing', $augmented->value());
        $this->assertEquals('missing', $augmented->label());
    }
}
