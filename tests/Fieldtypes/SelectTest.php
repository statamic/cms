<?php

namespace Tests\Fieldtypes;

use Statamic\Fields\Field;
use Statamic\Fields\LabeledValue;
use Statamic\Fieldtypes\Select;
use Tests\TestCase;

class SelectTest extends TestCase
{
    /** @test */
    public function it_casts_booleans_during_processing_when_enabled()
    {
        $field = (new Select)->setField(new Field('test', [
            'type' => 'select',
            'cast_booleans' => true,
            'options' => [
                'true' => 'Yup',
                'false' => 'Nope',
                'null' => 'Dunno',
                'foo' => 'Bar',
            ],
        ]));

        $this->assertEquals(true, $field->process('true'));
        $this->assertEquals(false, $field->process('false'));
        $this->assertEquals(null, $field->process('null'));
        $this->assertEquals('foo', $field->process('foo'));

        $this->assertEquals('true', $field->preProcess(true));
        $this->assertEquals('false', $field->preProcess(false));
        $this->assertEquals('null', $field->preProcess(null));
        $this->assertEquals('foo', $field->preProcess('foo'));
    }

    /** @test */
    public function it_casts_multiple_booleans_during_processing_when_enabled()
    {
        $field = (new Select)->setField(new Field('test', [
            'type' => 'select',
            'multiple' => true,
            'cast_booleans' => true,
            'options' => [
                'true' => 'Yup',
                'false' => 'Nope',
                'null' => 'Dunno',
                'foo' => 'Bar',
            ],
        ]));

        $this->assertEquals(
            [true, false, null, 'foo'],
            $field->process(['true', 'false', 'null', 'foo'])
        );

        $this->assertEquals(
            ['true', 'false', 'null', 'foo'],
            $field->preProcess([true, false, null, 'foo'])
        );
    }

    /** @test */
    public function it_doesnt_cast_booleans_during_processing_when_disabled()
    {
        $field = (new Select)->setField(new Field('test', [
            'type' => 'select',
            'cast_booleans' => false,
            'options' => [
                'true' => 'Yup',
                'false' => 'Nope',
                'null' => 'Dunno',
                'foo' => 'Bar',
            ],
        ]));

        $this->assertEquals('true', $field->process('true'));
        $this->assertEquals('false', $field->process('false'));
        $this->assertEquals('null', $field->process('null'));
        $this->assertEquals('foo', $field->process('foo'));

        $this->assertEquals(true, $field->preProcess(true));
        $this->assertEquals(false, $field->preProcess(false));
        $this->assertEquals(null, $field->preProcess(null));
        $this->assertEquals('foo', $field->preProcess('foo'));
    }

    /** @test */
    public function it_doesnt_cast_multiple_booleans_during_processing_when_disabled()
    {
        $field = (new Select)->setField(new Field('test', [
            'type' => 'select',
            'multiple' => true,
            'cast_booleans' => false,
            'options' => [
                'true' => 'Yup',
                'false' => 'Nope',
                'null' => 'Dunno',
                'foo' => 'Bar',
            ],
        ]));

        $this->assertEquals(
            ['true', 'false', 'null', 'foo'],
            $field->process(['true', 'false', 'null', 'foo'])
        );

        $this->assertEquals(
            [true, false, null, 'foo'],
            $field->preProcess([true, false, null, 'foo'])
        );
    }

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
            ['key' => true, 'value' => true, 'label' => true],
            ['key' => 'missing', 'value' => 'missing', 'label' => 'missing'],
        ], $field->augment(['au', 'us', null, false, true, 'missing']));
    }

    /** @test */
    public function it_augments_to_a_LabeledValue_object_with_boolean_casting()
    {
        $field = (new Select)->setField(new Field('test', [
            'type' => 'select',
            'cast_booleans' => true,
            'options' => [
                'true' => 'Yup',
                'false' => 'Nope',
            ],
        ]));

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
        $field = (new Select)->setField(new Field('test', [
            'type' => 'select',
            'cast_booleans' => true,
            'options' => [
                'true' => 'Yup',
                'false' => 'Nope',
                'null' => 'Dunno',
            ],
        ]));

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

    /** @test */
    public function it_augments_multiple_enabled_to_an_array_of_LabeledValue_equivalents_with_boolean_casting()
    {
        $field = (new Select)->setField(new Field('test', [
            'type' => 'select',
            'multiple' => true,
            'cast_booleans' => true,
            'options' => [
                'true' => 'Yup',
                'false' => 'Nope',
            ],
        ]));

        $this->assertEquals([
            ['key' => null, 'value' => null, 'label' => null],
            ['key' => false, 'value' => false, 'label' => 'Nope'],
            ['key' => true, 'value' => true, 'label' => 'Yup'],
            ['key' => 'missing', 'value' => 'missing', 'label' => 'missing'],
        ], $field->augment([null, false, true, 'missing']));
    }

    /** @test */
    public function it_augments_multiple_enabled_to_an_array_of_LabeledValue_equivalents_with_boolean_casting_and_a_null_option()
    {
        $field = (new Select)->setField(new Field('test', [
            'type' => 'select',
            'multiple' => true,
            'cast_booleans' => true,
            'options' => [
                'true' => 'Yup',
                'false' => 'Nope',
                'null' => 'Dunno',
            ],
        ]));

        $this->assertEquals([
            ['key' => null, 'value' => null, 'label' => 'Dunno'],
            ['key' => false, 'value' => false, 'label' => 'Nope'],
            ['key' => true, 'value' => true, 'label' => 'Yup'],
            ['key' => 'missing', 'value' => 'missing', 'label' => 'missing'],
        ], $field->augment([null, false, true, 'missing']));
    }
}
