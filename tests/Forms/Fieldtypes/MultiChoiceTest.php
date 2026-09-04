<?php

namespace Tests\Forms\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Forms\Charts\Pie;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Fieldtypes\MultiChoice;
use Tests\TestCase;

class MultiChoiceTest extends TestCase
{
    #[Test]
    public function it_returns_field_array()
    {
        $fieldtype = (new MultiChoice)->setField(new FormField('colors', [
            'type' => 'multi_choice',
            'options' => [
                'red' => 'Red',
                'blue' => 'Blue',
                'green' => 'Green',
            ],
        ]));

        $this->assertEquals([
            'type' => 'radio',
            'options' => [
                'red' => 'Red',
                'blue' => 'Blue',
                'green' => 'Green',
            ],
        ], $fieldtype->toFieldArray());
    }

    #[Test]
    public function it_passes_through_extra_config()
    {
        $fieldtype = (new MultiChoice)->setField(new FormField('colors', [
            'type' => 'multi_choice',
            'options' => [
                'red' => 'Red',
                'blue' => 'Blue',
            ],
            'default' => 'red',
        ]));

        $this->assertEquals([
            'type' => 'radio',
            'options' => [
                'red' => 'Red',
                'blue' => 'Blue',
            ],
            'default' => 'red',
        ], $fieldtype->toFieldArray());
    }

    #[Test]
    public function it_defaults_to_a_pie_chart()
    {
        $this->assertEquals(Pie::class, (new MultiChoice)->defaultChart());
    }

    #[Test]
    public function it_returns_its_options_as_chart_options()
    {
        $fieldtype = (new MultiChoice)->setField(new FormField('color', [
            'type' => 'multi_choice',
            'options' => ['red' => 'Red', 'blue' => 'Blue'],
        ]));

        $options = $fieldtype->chartOptions(collect());

        $this->assertEquals(['red', 'blue'], $options->map->key->all());
        $this->assertEquals(['Red', 'Blue'], $options->map->label->all());
    }

    #[Test]
    public function it_excludes_hidden_options_from_chart_options()
    {
        $fieldtype = (new MultiChoice)->setField(new FormField('color', [
            'type' => 'multi_choice',
            'options' => [
                ['key' => 'red', 'value' => 'Red'],
                ['key' => 'blue', 'value' => 'Blue', 'hidden' => true],
            ],
        ]));

        $this->assertEquals(['red'], $fieldtype->chartOptions(collect())->map->key->all());
    }
}
