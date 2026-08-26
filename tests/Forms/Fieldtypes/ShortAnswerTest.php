<?php

namespace Tests\Forms\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Forms\Charts\HorizontalBar;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Fieldtypes\ShortAnswer;
use Tests\TestCase;

class ShortAnswerTest extends TestCase
{
    #[Test]
    public function it_returns_field_array()
    {
        $fieldtype = (new ShortAnswer)->setField(new FormField('name', [
            'type' => 'short_answer',
            'placeholder' => 'Your name',
        ]));

        $this->assertEquals([
            'type' => 'text',
            'placeholder' => 'Your name',
        ], $fieldtype->toFieldArray());
    }

    #[Test]
    public function it_passes_through_extra_config()
    {
        $fieldtype = (new ShortAnswer)->setField(new FormField('name', [
            'type' => 'short_answer',
            'placeholder' => 'Your name',
            'default' => 'David Hasselhoff',
        ]));

        $this->assertEquals([
            'type' => 'text',
            'placeholder' => 'Your name',
            'default' => 'David Hasselhoff',
        ], $fieldtype->toFieldArray());
    }

    #[Test]
    public function it_defaults_to_a_bar_chart_counting_unique_answers()
    {
        $fieldtype = (new ShortAnswer);

        $this->assertEquals(HorizontalBar::class, $fieldtype->defaultChart());
        $this->assertNull($fieldtype->chartOptions(collect()));
    }
}
