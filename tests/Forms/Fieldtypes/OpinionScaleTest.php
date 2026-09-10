<?php

namespace Tests\Forms\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Forms\Charts\VerticalBar;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Fieldtypes\OpinionScale;
use Statamic\Forms\Insights\Average;
use Tests\TestCase;

class OpinionScaleTest extends TestCase
{
    #[Test]
    public function it_returns_field_array()
    {
        $fieldtype = (new OpinionScale)->setField(new FormField('satisfaction', [
            'type' => 'opinion_scale',
        ]));

        $this->assertEquals([
            'type' => 'opinion_scale',
            'min' => 0,
            'max' => 10,
            'low_label' => null,
            'middle_label' => null,
            'high_label' => null,
        ], $fieldtype->toFieldArray());
    }

    #[Test]
    public function it_passes_through_labels_and_normalizes_range()
    {
        $fieldtype = (new OpinionScale)->setField(new FormField('satisfaction', [
            'type' => 'opinion_scale',
            'display' => 'How satisfied are you?',
            'min' => 1,
            'max' => 5,
            'low_label' => 'Not satisfied',
            'middle_label' => 'Neutral',
            'high_label' => 'Very satisfied',
        ]));

        $this->assertEquals([
            'type' => 'opinion_scale',
            'min' => 1,
            'max' => 5,
            'low_label' => 'Not satisfied',
            'middle_label' => 'Neutral',
            'high_label' => 'Very satisfied',
            'display' => 'How satisfied are you?',
        ], $fieldtype->toFieldArray());
    }

    #[Test]
    public function it_clamps_min_and_max_to_a_sensible_range()
    {
        $fieldtype = (new OpinionScale)->setField(new FormField('satisfaction', [
            'type' => 'opinion_scale',
            'min' => -5,
            'max' => 25,
        ]));

        $this->assertEquals(0, $fieldtype->toFieldArray()['min']);
        $this->assertEquals(10, $fieldtype->toFieldArray()['max']);

        $fieldtype = (new OpinionScale)->setField(new FormField('satisfaction', [
            'type' => 'opinion_scale',
            'min' => 5,
            'max' => 10,
        ]));

        $this->assertEquals(5, $fieldtype->toFieldArray()['min']);
        $this->assertEquals(10, $fieldtype->toFieldArray()['max']);
    }

    #[Test]
    public function it_defaults_to_a_column_chart()
    {
        $this->assertEquals(VerticalBar::class, (new OpinionScale)->defaultChart());
    }

    #[Test]
    public function it_returns_its_range_as_chart_options()
    {
        $fieldtype = (new OpinionScale)->setField(new FormField('satisfaction', [
            'type' => 'opinion_scale',
            'min' => 1,
            'max' => 5,
        ]));

        $this->assertEquals(['1', '2', '3', '4', '5'], $fieldtype->chartOptions(collect())->map->key->all());
    }

    #[Test]
    public function it_returns_an_average_insight()
    {
        $insights = (new OpinionScale)->setField(new FormField('satisfaction', ['type' => 'opinion_scale']))->insights();

        $this->assertCount(1, $insights);
        $this->assertInstanceOf(Average::class, $insights[0]);
    }
}
