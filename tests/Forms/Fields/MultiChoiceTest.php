<?php

namespace Tests\Forms\Fields;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Fields\MultiChoice;
use Tests\TestCase;

class MultiChoiceTest extends TestCase
{
    #[Test]
    public function it_returns_field_array()
    {
        $fieldtype = (new MultiChoice)->setField(new FormField('colors', [
            'type' => 'multi_choice',
            'placeholder' => 'Choose colors',
            'options' => [
                'red' => 'Red',
                'blue' => 'Blue',
                'green' => 'Green',
            ],
        ]));

        $this->assertEquals([
            'type' => 'select',
            'multiple' => true,
            'max_items' => null,
            'options' => [
                'red' => 'Red',
                'blue' => 'Blue',
                'green' => 'Green',
            ],
            'placeholder' => 'Choose colors',
        ], $fieldtype->toFieldArray());
    }

    #[Test]
    public function it_maps_max_selections_to_max_items()
    {
        $fieldtype = (new MultiChoice)->setField(new FormField('colors', [
            'type' => 'multi_choice',
            'placeholder' => 'Choose colors',
            'max_selections' => 3,
            'options' => [
                'red' => 'Red',
                'blue' => 'Blue',
                'green' => 'Green',
            ],
        ]));

        $this->assertEquals([
            'type' => 'select',
            'multiple' => true,
            'max_items' => 3,
            'options' => [
                'red' => 'Red',
                'blue' => 'Blue',
                'green' => 'Green',
            ],
            'placeholder' => 'Choose colors',
        ], $fieldtype->toFieldArray());
    }

    #[Test]
    public function it_passes_through_extra_config()
    {
        $fieldtype = (new MultiChoice)->setField(new FormField('colors', [
            'type' => 'multi_choice',
            'placeholder' => 'Choose colors',
            'options' => [
                'red' => 'Red',
                'blue' => 'Blue',
            ],
            'default' => 'red',
        ]));

        $this->assertEquals([
            'type' => 'select',
            'multiple' => true,
            'max_items' => null,
            'options' => [
                'red' => 'Red',
                'blue' => 'Blue',
            ],
            'placeholder' => 'Choose colors',
            'default' => 'red',
        ], $fieldtype->toFieldArray());
    }
}
