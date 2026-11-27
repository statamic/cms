<?php

namespace Statamic\Forms\Fieldtypes;

use Statamic\Forms\Fields\FormFieldtype;
use Statamic\Support\Arr;

use function Statamic\trans as __;

class DatePicker extends FormFieldtype
{
    protected static $fieldtype = 'date';
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
                'display' => __('statamic::form-fieldtypes.date_picker.example.display'),
            ],
            'value' => '1990-01-15',
        ];
    }
}
