<?php

namespace Tests\Forms\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Fieldtypes\TimePicker;
use Tests\TestCase;

class TimePickerTest extends TestCase
{
    #[Test]
    public function it_returns_field_array()
    {
        $fieldtype = (new TimePicker)->setField(new FormField('start_time', [
            'type' => 'time_picker',
        ]));

        $this->assertEquals([
            'type' => 'time',
        ], $fieldtype->toFieldArray());
    }

    #[Test]
    public function it_passes_through_extra_config()
    {
        $fieldtype = (new TimePicker)->setField(new FormField('start_time', [
            'type' => 'time_picker',
            'default' => '09:00',
        ]));

        $this->assertEquals([
            'type' => 'time',
            'default' => '09:00',
        ], $fieldtype->toFieldArray());
    }
}
