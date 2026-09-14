<?php

namespace Tests\Forms\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Forms\Charts\HorizontalBar;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Fieldtypes\Dropdown;
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
            'options' => [
                'red' => 'Red',
                'blue' => 'Blue',
                'green' => 'Green',
            ],
            'placeholder' => 'Choose a color',
            'multiple' => null,
            'max_items' => null,
        ], $fieldtype->toFieldArray());
    }

    #[Test]
    public function it_returns_field_array_with_multiple()
    {
        $fieldtype = (new Dropdown)->setField(new FormField('colors', [
            'type' => 'dropdown',
            'placeholder' => 'Choose colors',
            'options' => [
                'red' => 'Red',
                'blue' => 'Blue',
                'green' => 'Green',
            ],
            'multiple' => true,
            'max_selections' => 2,
        ]));

        $this->assertEquals([
            'type' => 'select',
            'options' => [
                'red' => 'Red',
                'blue' => 'Blue',
                'green' => 'Green',
            ],
            'placeholder' => 'Choose colors',
            'multiple' => true,
            'max_items' => 2,
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
            'options' => [
                'red' => 'Red',
                'blue' => 'Blue',
            ],
            'placeholder' => 'Choose a color',
            'multiple' => null,
            'max_items' => null,
            'default' => 'red',
        ], $fieldtype->toFieldArray());
    }

    #[Test]
    public function it_defaults_to_a_bar_chart()
    {
        $this->assertEquals(HorizontalBar::class, (new Dropdown)->defaultChart());
    }

    #[Test]
    public function it_returns_its_options_as_chart_options()
    {
        $fieldtype = (new Dropdown)->setField(new FormField('seen', [
            'type' => 'dropdown',
            'options' => ['yep' => 'Yep', 'nope' => 'Nope'],
        ]));

        $options = $fieldtype->chartOptions(collect());

        $this->assertEquals(['yep', 'nope'], $options->map->key->all());
        $this->assertEquals(['Yep', 'Nope'], $options->map->label->all());
    }
}
