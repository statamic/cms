<?php

namespace Statamic\Forms\Fields;

use Statamic\Support\Arr;

class Dropdown extends FormFieldtype
{
    protected static $fieldtype = 'select';
    protected $icon = 'fieldtype-select';
    protected $categories = ['Choice'];

    public function configFieldItems(): array
    {
        return [
            'placeholder' => [
                'display' => __('Placeholder'),
                'instructions' => __('statamic::fieldtypes.select.config.placeholder'),
                'type' => 'text',
                'default' => '',
                'width' => '50',
            ],
            'options' => [
                'display' => __('Options'),
                'instructions' => __('statamic::fieldtypes.select.config.options'),
                'type' => 'array',
                'expand' => true,
                'key_header' => __('Key'),
                'value_header' => __('Label').' ('.__('Optional').')',
                'add_button' => __('Add Option'),
                'width' => '50',
            ],
        ];
    }

    public function toFieldArray(): array
    {
        return [
            'type' => 'select',
            'max_items' => 1,
            'options' => $this->config('options'),
            'placeholder' => $this->config('placeholder'),
            ...Arr::except($this->config(), ['type', 'options', 'placeholder']),
        ];
    }
}
