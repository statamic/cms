<?php

namespace Tests\Forms\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Fieldtypes\DatePicker;
use Tests\TestCase;

class DatePickerTest extends TestCase
{
    #[Test]
    public function it_returns_field_array()
    {
        $fieldtype = (new DatePicker)->setField(new FormField('birthday', [
            'type' => 'date_picker',
        ]));

        $this->assertEquals([
            'type' => 'date',
            'format' => 'Y-m-d',
        ], $fieldtype->toFieldArray());
    }

    #[Test]
    public function it_passes_through_extra_config()
    {
        $fieldtype = (new DatePicker)->setField(new FormField('birthday', [
            'type' => 'date_picker',
            'default' => '2000-01-01',
        ]));

        $this->assertEquals([
            'type' => 'date',
            'format' => 'Y-m-d',
            'default' => '2000-01-01',
        ], $fieldtype->toFieldArray());
    }
}
