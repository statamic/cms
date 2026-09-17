<?php

namespace Tests\Forms\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Forms\Charts\HorizontalBar;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Fieldtypes\Checkboxes;
use Tests\TestCase;

class CheckboxesTest extends TestCase
{
    #[Test]
    public function it_returns_field_array()
    {
        $fieldtype = (new Checkboxes)->setField(new FormField('interests', [
            'type' => 'checkboxes',
            'options' => [
                'music' => 'Music',
                'sports' => 'Sports',
                'reading' => 'Reading',
            ],
        ]));

        $this->assertEquals([
            'type' => 'checkboxes',
            'options' => [
                'music' => 'Music',
                'sports' => 'Sports',
                'reading' => 'Reading',
            ],
        ], $fieldtype->toFieldArray());
    }

    #[Test]
    public function it_passes_through_extra_config()
    {
        $fieldtype = (new Checkboxes)->setField(new FormField('interests', [
            'type' => 'checkboxes',
            'options' => [
                'music' => 'Music',
                'sports' => 'Sports',
            ],
            'default' => ['music'],
        ]));

        $this->assertEquals([
            'type' => 'checkboxes',
            'options' => [
                'music' => 'Music',
                'sports' => 'Sports',
            ],
            'default' => ['music'],
        ], $fieldtype->toFieldArray());
    }

    #[Test]
    public function it_defaults_to_a_bar_chart()
    {
        $this->assertEquals(HorizontalBar::class, (new Checkboxes)->defaultChart());
    }

    #[Test]
    public function it_returns_its_options_as_chart_options_with_checkbox_icons()
    {
        $fieldtype = (new Checkboxes)->setField(new FormField('interests', [
            'type' => 'checkboxes',
            'options' => ['music' => 'Music', 'sports' => 'Sports'],
        ]));

        $options = $fieldtype->chartOptions(collect());

        $this->assertEquals(['music', 'sports'], $options->map->key->all());
        $this->assertEquals(['Music', 'Sports'], $options->map->label->all());
        $this->assertEquals(['checkbox-filled', 'checkbox-filled'], $options->map->icon->all());
    }
}
