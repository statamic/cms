<?php

namespace Statamic\Forms\Fieldtypes;

use Statamic\Forms\Fields\FormFieldtype;
use Statamic\Support\Arr;

class TimePicker extends FormFieldtype
{
    protected static $fieldtype = 'time';
    protected $description = 'Lets respondents pick a time of day.';
    protected $icon = 'time-clock';
    protected $categories = ['datetime'];

    public function toFieldArray(): array
    {
        return [
            'type' => 'time',
            ...Arr::except($this->config(), ['type']),
        ];
    }

    public function example(): ?array
    {
        return [
            'config' => [
                'display' => 'When do you usually eat lunch?',
            ],
            'value' => '12:30',
        ];
    }
}
