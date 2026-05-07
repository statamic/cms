<?php

namespace Statamic\Forms\Fields;

use Statamic\Support\Arr;

class TimePicker extends FormFieldtype
{
    protected static $fieldtype = 'time';
    protected $icon = 'time-clock';
    protected $categories = ['Date and Time'];

    public function toFieldArray(): array
    {
        return [
            'type' => 'time',
            ...Arr::except($this->config(), ['type']),
        ];
    }
}
