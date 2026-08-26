<?php

namespace Tests\Forms\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Forms\Charts\HorizontalBar;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Fieldtypes\Phone;
use Tests\TestCase;

class PhoneTest extends TestCase
{
    #[Test]
    public function it_returns_field_array_with_tel_input_type()
    {
        $fieldtype = (new Phone)->setField(new FormField('phone', [
            'type' => 'phone',
            'placeholder' => '(555) 123-4567',
        ]));

        $this->assertEquals([
            'type' => 'text',
            'input_type' => 'tel',
            'placeholder' => '(555) 123-4567',
        ], $fieldtype->toFieldArray());
    }

    #[Test]
    public function it_passes_through_extra_config()
    {
        $fieldtype = (new Phone)->setField(new FormField('phone', [
            'type' => 'phone',
            'placeholder' => '(555) 123-4567',
            'default' => '(555) 000-0000',
        ]));

        $this->assertEquals([
            'type' => 'text',
            'input_type' => 'tel',
            'placeholder' => '(555) 123-4567',
            'default' => '(555) 000-0000',
        ], $fieldtype->toFieldArray());
    }

    #[Test]
    public function it_defaults_to_a_bar_chart_counting_unique_answers()
    {
        $fieldtype = (new Phone);

        $this->assertEquals(HorizontalBar::class, $fieldtype->defaultChart());
        $this->assertNull($fieldtype->chartOptions(collect()));
    }
}
