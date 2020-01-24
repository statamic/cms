<?php

namespace Tests\Fieldtypes;

use Statamic\Facades\Antlers;
use Statamic\Fields\Field;
use Statamic\Fields\Value;
use Statamic\Fieldtypes\Checkboxes;
use Tests\TestCase;

class CheckboxesTest extends TestCase
{
    /** @test */
    function it_augments_to_empty_array_when_null()
    {
        $this->assertEquals([], $this->fieldtype()->augment(null));
    }

    /** @test */
    function it_augments_to_LabeledValue_equivalents_for_looping()
    {
        $this->assertEquals([
            ['key' => 'au', 'value' => 'au', 'label' => 'Australia'],
            ['key' => 'ca', 'value' => 'ca', 'label' => 'Canada'],
        ], $this->fieldtype()->augment(['au', 'ca']));
    }

    /** @test */
    function it_augments_to_LabeledValue_equivalents_for_looping_with_no_keys()
    {
        $fieldtype = $this->fieldtype([
            'options' => [
                'au',
                'ca',
                'us',
            ]
        ]);

        $this->assertEquals([
            ['key' => 'au', 'value' => 'au', 'label' => 'au'],
            ['key' => 'ca', 'value' => 'ca', 'label' => 'ca'],
        ], $fieldtype->augment(['au', 'ca']));
    }

    function fieldtype($config = [])
    {
        return (new Checkboxes)->setField(new Field('test', array_merge([
            'type' => 'checkboxes',
            'options' => [
                'au' => 'Australia',
                'ca' => 'Canada',
                'us' => 'USA',
            ]
        ], $config)));
    }
}
