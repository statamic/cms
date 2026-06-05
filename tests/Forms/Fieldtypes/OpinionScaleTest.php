<?php

namespace Tests\Forms\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Fieldtypes\OpinionScale;
use Tests\TestCase;

class OpinionScaleTest extends TestCase
{
    #[Test]
    public function it_returns_field_array_with_defaults()
    {
        $fieldtype = (new OpinionScale)->setField(new FormField('satisfaction', [
            'type' => 'opinion_scale',
        ]));

        $this->assertEquals([
            'type' => 'opinion_scale',
            'min' => 0,
            'max' => 10,
            'scale_values' => range(0, 10),
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
            'scale_values' => range(1, 5),
            'low_label' => 'Not satisfied',
            'middle_label' => 'Neutral',
            'high_label' => 'Very satisfied',
            'display' => 'How satisfied are you?',
        ], $fieldtype->toFieldArray());
    }

    #[Test]
    public function it_clamps_max_to_a_sensible_range()
    {
        $fieldtype = (new OpinionScale)->setField(new FormField('satisfaction', [
            'type' => 'opinion_scale',
            'min' => 0,
            'max' => 25,
        ]));

        $this->assertEquals(10, $fieldtype->toFieldArray()['max']);

        $fieldtype = (new OpinionScale)->setField(new FormField('satisfaction', [
            'type' => 'opinion_scale',
            'min' => 1,
            'max' => 1,
        ]));

        $this->assertEquals(2, $fieldtype->toFieldArray()['max']);
    }

    #[Test]
    public function it_provides_an_example()
    {
        $example = (new OpinionScale)->example();

        $this->assertSame('How likely are you to recommend us?', $example['config']['display']);
        $this->assertEquals(0, $example['config']['min']);
        $this->assertEquals(10, $example['config']['max']);
        $this->assertEquals('Not likely', $example['config']['low_label']);
        $this->assertEquals('Very likely', $example['config']['high_label']);
        $this->assertSame(8, $example['value']);
    }
}
