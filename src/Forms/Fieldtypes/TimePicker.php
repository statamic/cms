<?php

namespace Statamic\Forms\Fieldtypes;

use Statamic\Forms\Fields\FormFieldtype;
use Statamic\Support\Arr;

use function Statamic\trans as __;

class TimePicker extends FormFieldtype
{
    protected static $fieldtype = 'time';
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
                'display' => __('statamic::form-fieldtypes.time_picker.example.display'),
            ],
            'value' => '12:30',
        ];
    }
}
