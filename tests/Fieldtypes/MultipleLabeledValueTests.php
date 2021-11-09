<?php

namespace Tests\Fieldtypes;

trait MultipleLabeledValueTests
{
    /** @test */
    public function it_augments_to_empty_array_when_null_and_configured_for_multiple()
    {
        $this->assertEquals([], $this->field(['multiple' => true])->augment(null));
    }

    /** @test */
    public function it_augments_multiple_enabled_to_an_array_of_LabeledValue_equivalents()
    {
        $field = $this->field([
            'type' => 'select',
            'multiple' => true,
            'options' => [
                'au' => 'Australia',
                'ca' => 'Canada',
                'us' => 'USA',
            ],
        ]);

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
    public function it_augments_multiple_enabled_to_an_array_of_LabeledValue_equivalents_with_numeric_keys()
    {
        $field = $this->field([
            'type' => 'select',
            'multiple' => true,
            'options' => [
                1 => 'Australia',
                2 => 'Canada',
                3 => 'USA',
            ],
        ]);

        $this->assertEquals([
            ['key' => 2, 'value' => 2, 'label' => 'Canada'],
            ['key' => null, 'value' => null, 'label' => null],
            ['key' => false, 'value' => false, 'label' => false],
            ['key' => true, 'value' => true, 'label' => true],
            ['key' => 'missing', 'value' => 'missing', 'label' => 'missing'],
        ], $field->augment([2, null, false, true, 'missing']));

        $this->assertEquals([
            ['key' => 2, 'value' => 2, 'label' => 'Canada'],
        ], $field->augment(['2']));

        $this->assertEquals([
            ['key' => '2.5', 'value' => '2.5', 'label' => '2.5'],
        ], $field->augment(['2.5']));
    }

    /** @test */
    public function it_augments_multiple_enabled_to_an_array_of_LabeledValue_equivalents_with_floats_for_keys()
    {
        $field = $this->field([
            'type' => 'select',
            'multiple' => true,
            'options' => [
                '1.5' => 'One point five',
                '2.5' => 'Two point five',
            ],
        ]);

        $this->assertEquals([
            ['key' => '2.5', 'value' => '2.5', 'label' => 'Two point five'],
        ], $field->augment(['2.5']));

        $this->assertEquals([
            ['key' => '2.5', 'value' => '2.5', 'label' => 'Two point five'],
        ], $field->augment([2.5]));
    }

    /** @test */
    public function it_augments_multiple_enabled_to_an_array_of_LabeledValue_equivalents_with_boolean_casting()
    {
        $field = $this->field([
            'type' => 'select',
            'multiple' => true,
            'cast_booleans' => true,
            'options' => [
                'true' => 'Yup',
                'false' => 'Nope',
            ],
        ]);

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
        $field = $this->field([
            'type' => 'select',
            'multiple' => true,
            'cast_booleans' => true,
            'options' => [
                'true' => 'Yup',
                'false' => 'Nope',
                'null' => 'Dunno',
            ],
        ]);

        $this->assertEquals([
            ['key' => null, 'value' => null, 'label' => 'Dunno'],
            ['key' => false, 'value' => false, 'label' => 'Nope'],
            ['key' => true, 'value' => true, 'label' => 'Yup'],
            ['key' => 'missing', 'value' => 'missing', 'label' => 'missing'],
        ], $field->augment([null, false, true, 'missing']));
    }

    /**
     * @test
     * @dataProvider noMultipleOptionsProvider
     */
    public function it_augments_multiple_enabled_to_an_array_of_LabeledValue_equivalents_with_no_options($options)
    {
        $field = $this->field([
            'type' => 'select',
            'multiple' => true,
            'options' => $options,
        ]);

        $this->assertEquals([
            ['key' => null, 'value' => null, 'label' => null],
            ['key' => false, 'value' => false, 'label' => false],
            ['key' => true, 'value' => true, 'label' => true],
            ['key' => 'missing', 'value' => 'missing', 'label' => 'missing'],
        ], $field->augment([null, false, true, 'missing']));
    }

    public function noMultipleOptionsProvider()
    {
        return [
            'empty_array' => [[]],
            'null' => [null],
        ];
    }
}
