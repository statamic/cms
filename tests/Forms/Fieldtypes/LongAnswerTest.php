<?php

namespace Tests\Forms\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Forms\Charts\HorizontalBar;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Fieldtypes\LongAnswer;
use Tests\TestCase;

class LongAnswerTest extends TestCase
{
    #[Test]
    public function it_returns_field_array()
    {
        $fieldtype = (new LongAnswer)->setField(new FormField('message', [
            'type' => 'long_answer',
            'placeholder' => 'Your message',
        ]));

        $this->assertEquals([
            'type' => 'textarea',
            'placeholder' => 'Your message',
        ], $fieldtype->toFieldArray());
    }

    #[Test]
    public function it_passes_through_extra_config()
    {
        $fieldtype = (new LongAnswer)->setField(new FormField('message', [
            'type' => 'long_answer',
            'placeholder' => 'Your message',
            'default' => 'David Hasselhoff',
        ]));

        $this->assertEquals([
            'type' => 'textarea',
            'placeholder' => 'Your message',
            'default' => 'David Hasselhoff',
        ], $fieldtype->toFieldArray());
    }

    #[Test]
    public function it_defaults_to_a_bar_chart_counting_unique_answers()
    {
        $fieldtype = (new LongAnswer);

        $this->assertEquals(HorizontalBar::class, $fieldtype->defaultChart());
        $this->assertNull($fieldtype->chartOptions(collect()));
    }
}
