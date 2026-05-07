<?php

namespace Statamic\Forms\Fields;

use Statamic\Support\Arr;

class Checkboxes extends FormFieldtype
{
    protected static $fieldtype = 'checkboxes';
    protected $icon = 'fieldtype-checkboxes';
    protected $categories = ['Choice'];

    public function configFieldItems(): array
    {
        return [
            'options' => [
                'display' => __('Options'),
                'instructions' => __('statamic::fieldtypes.checkboxes.config.options'),
                'type' => 'array',
                'expand' => true,
                'key_header' => __('Key'),
                'value_header' => __('Label').' ('.__('Optional').')',
                'add_button' => __('Add Option'),
            ],
        ];
    }

    public function toFieldArray(): array
    {
        return [
            'type' => 'checkboxes',
            'options' => $this->config('options'),
            ...Arr::except($this->config(), ['type', 'options']),
        ];
    }
}
