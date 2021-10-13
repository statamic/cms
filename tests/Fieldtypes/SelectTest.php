<?php

namespace Tests\Fieldtypes;

use Statamic\Fields\Field;
use Statamic\Fields\LabeledValue;
use Statamic\Fieldtypes\Select;
use Tests\TestCase;

class SelectTest extends TestCase
{
    /** @test */
    public function it_augments_to_a_LabeledValue_object_with_options_with_keys()
    {
        $field = (new Select)->setField(new Field('test', [
            'type' => 'select',
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

        $augmented = $field->augment(null);
        $this->assertInstanceOf(LabeledValue::class, $augmented);
        $this->assertNull($augmented->value());
        $this->assertNull($augmented->label());

        $augmented = $field->augment(false);
        $this->assertInstanceOf(LabeledValue::class, $augmented);
        $this->assertFalse($augmented->value());
        $this->assertFalse($augmented->label());

        $augmented = $field->augment('missing');
        $this->assertInstanceOf(LabeledValue::class, $augmented);
        $this->assertEquals('missing', $augmented->value());
        $this->assertEquals('missing', $augmented->label());
    }

    /** @test */
    public function it_augments_to_a_LabeledValue_object_with_options_without_keys()
    {
        $field = (new Select)->setField(new Field('test', [
            'type' => 'select',
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

        $augmented = $field->augment(null);
        $this->assertInstanceOf(LabeledValue::class, $augmented);
        $this->assertNull($augmented->value());
        $this->assertNull($augmented->label());

        $augmented = $field->augment(false);
        $this->assertInstanceOf(LabeledValue::class, $augmented);
        $this->assertFalse($augmented->value());
        $this->assertFalse($augmented->label());

        $augmented = $field->augment('missing');
        $this->assertInstanceOf(LabeledValue::class, $augmented);
        $this->assertEquals('missing', $augmented->value());
        $this->assertEquals('missing', $augmented->label());
    }

    /** @test */
    public function it_augments_to_a_LabeledValue_object_with_a_null_value()
    {
        $field = (new Select)->setField(new Field('test', [
            'type' => 'select',
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

        $augmented = $field->augment(null);
        $this->assertInstanceOf(LabeledValue::class, $augmented);
        $this->assertNull($augmented->value());
        $this->assertNull($augmented->label());

        $augmented = $field->augment(false);
        $this->assertInstanceOf(LabeledValue::class, $augmented);
        $this->assertFalse($augmented->value());
        $this->assertFalse($augmented->label());

        $augmented = $field->augment('missing');
        $this->assertInstanceOf(LabeledValue::class, $augmented);
        $this->assertEquals('missing', $augmented->value());
        $this->assertEquals('missing', $augmented->label());
    }

    /** @test */
    public function it_augments_multiple_enabled_to_an_array_of_LabeledValue_equivalents()
    {
        $field = (new Select)->setField(new Field('test', [
            'type' => 'select',
            'multiple' => true,
            'options' => [
                'au' => 'Australia',
                'ca' => 'Canada',
                'us' => 'USA',
            ],
        ]));

        $this->assertEquals([
            ['key' => 'au', 'value' => 'au', 'label' => 'Australia'],
            ['key' => 'us', 'value' => 'us', 'label' => 'USA'],
            ['key' => null, 'value' => null, 'label' => null],
            ['key' => false, 'value' => false, 'label' => false],
            ['key' => 'missing', 'value' => 'missing', 'label' => 'missing'],
        ], $field->augment(['au', 'us', null, false, 'missing']));
    }
}
