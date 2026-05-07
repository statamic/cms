<?php

namespace Tests\Forms\Fields;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Forms\Fields\Dropdown;
use Statamic\Forms\Fields\FormField;
use Tests\TestCase;

class DropdownTest extends TestCase
{
    #[Test]
    public function it_returns_field_array()
    {
        $fieldtype = (new Dropdown)->setField(new FormField('color', [
            'type' => 'dropdown',
            'placeholder' => 'Choose a color',
            'options' => [
                'red' => 'Red',
                'blue' => 'Blue',
                'green' => 'Green',
            ],
        ]));

        $this->assertEquals([
            'type' => 'select',
            'max_items' => 1,
            'options' => [
                'red' => 'Red',
                'blue' => 'Blue',
                'green' => 'Green',
            ],
            'placeholder' => 'Choose a color',
        ], $fieldtype->toFieldArray());
    }

    #[Test]
    public function it_passes_through_extra_config()
    {
        $fieldtype = (new Dropdown)->setField(new FormField('color', [
            'type' => 'dropdown',
            'placeholder' => 'Choose a color',
            'options' => [
                'red' => 'Red',
                'blue' => 'Blue',
            ],
            'default' => 'red',
        ]));

        $this->assertEquals([
            'type' => 'select',
            'max_items' => 1,
            'options' => [
                'red' => 'Red',
                'blue' => 'Blue',
            ],
            'placeholder' => 'Choose a color',
            'default' => 'red',
        ], $fieldtype->toFieldArray());
    }
}
