<?php

namespace Tests\Fieldtypes;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Fields\LabeledValue;

trait LabeledValueTests
{
    #[Test]
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

    #[Test]
    public function it_augments_to_a_LabeledValue_object_with_options_with_numeric_keys()
    {
        $field = $this->field([
            'type' => 'select',
            'options' => [
                1 => 'Australia',
                2 => 'Canada',
                '2.5' => 'Canada and a half',
                3 => 'USA',
            ],
        ]);

        $augmented = $field->augment(2);
        $this->assertInstanceOf(LabeledValue::class, $augmented);
        $this->assertEquals(2, $augmented->value());
        $this->assertEquals('Canada', $augmented->label());

        // Javascript converts numeric keys to strings. Thanks.
        // People will end up with string in their data. We should treat it like a number though.
        $augmented = $field->augment('2');
        $this->assertInstanceOf(LabeledValue::class, $augmented);
        $this->assertEquals(2, $augmented->value());
        $this->assertEquals('Canada', $augmented->label());

        // Just a sanity check that floats aren't converted to ints.
        // You can't have a float as a key in an array.
        $augmented = $field->augment(2.5);
        $this->assertInstanceOf(LabeledValue::class, $augmented);
        $this->assertEquals('2.5', $augmented->value());
        $this->assertEquals('Canada and a half', $augmented->label());

        // and again for the string version
        $augmented = $field->augment('2.5');
        $this->assertInstanceOf(LabeledValue::class, $augmented);
        $this->assertEquals('2.5', $augmented->value());
        $this->assertEquals('Canada and a half', $augmented->label());

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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
    #[DataProvider('noOptionsProvider')]
    public function it_augments_to_a_LabeledValue_object_with_no_options($options)
    {
        $field = $this->field([
            'type' => 'select',
            'options' => $options,
        ]);

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

    public static function noOptionsProvider()
    {
        return [
            'empty_array' => [[]],
            'null' => [null],
        ];
    }
}
