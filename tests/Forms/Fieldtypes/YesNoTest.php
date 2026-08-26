<?php

namespace Tests\Forms\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Forms\Charts\HorizontalBar;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Fieldtypes\YesNo;
use Tests\TestCase;

class YesNoTest extends TestCase
{
    #[Test]
    public function it_returns_field_array_with_yes_no_options()
    {
        $fieldtype = (new YesNo)->setField(new FormField('recommend', [
            'type' => 'yes_no',
        ]));

        $this->assertEquals([
            'type' => 'yes_no',
            'appearance' => 'chips',
            'options' => [
                'yes' => 'Yes',
                'no' => 'No',
            ],
        ], $fieldtype->toFieldArray());
    }

    #[Test]
    public function it_passes_through_extra_config()
    {
        $fieldtype = (new YesNo)->setField(new FormField('recommend', [
            'type' => 'yes_no',
            'display' => 'Would you recommend this product?',
        ]));

        $this->assertEquals([
            'type' => 'yes_no',
            'appearance' => 'chips',
            'options' => [
                'yes' => 'Yes',
                'no' => 'No',
            ],
            'display' => 'Would you recommend this product?',
        ], $fieldtype->toFieldArray());
    }

    #[Test]
    public function it_defaults_to_a_bar_chart()
    {
        $this->assertEquals(HorizontalBar::class, (new YesNo)->defaultChart());
    }

    #[Test]
    public function it_returns_yes_and_no_chart_options()
    {
        $options = (new YesNo)->setField(new FormField('pint', ['type' => 'yes_no']))->chartOptions(collect());

        $this->assertEquals(['yes', 'no'], $options->map->key->all());
        $this->assertEquals(['Yes', 'No'], $options->map->label->all());
        $this->assertEquals(['checkmark-circle-filled', 'delete-circle-filled'], $options->map->icon->all());
    }
}
