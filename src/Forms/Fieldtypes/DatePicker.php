<?php

namespace Statamic\Forms\Fieldtypes;

use Statamic\Forms\Fields\FormFieldtype;
use Statamic\Support\Arr;

class DatePicker extends FormFieldtype
{
    protected static $fieldtype = 'date';
    protected $description = 'Lets respondents pick a date.';
    protected $icon = 'calendar';
    protected $categories = ['datetime'];

    public function toFieldArray(): array
    {
        return [
            'type' => 'date',
            'format' => 'Y-m-d',
            ...Arr::except($this->config(), ['type']),
        ];
    }

    public function example(): ?array
    {
        return [
            'config' => [
                'display' => 'What is your birthday?',
            ],
            'value' => '1990-01-15',
        ];
    }
}
