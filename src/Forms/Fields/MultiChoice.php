<?php

namespace Statamic\Forms\Fields;

use Statamic\Support\Arr;

class MultiChoice extends FormFieldtype
{
    protected static $fieldtype = 'select';
    protected $icon = 'fieldtype-radio';
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
            'max_selections' => [
                'display' => __('Max Selections'),
                'instructions' => __('statamic::fieldtypes.select.config.max_items'),
                'type' => 'integer',
                'width' => '50',
            ],
        ];
    }

    public function toFieldArray(): array
    {
        return [
            'type' => 'select',
            'multiple' => true,
            'max_items' => $this->config('max_selections'),
            'options' => $this->config('options'),
            'placeholder' => $this->config('placeholder'),
            ...Arr::except($this->config(), ['type', 'options', 'placeholder', 'max_selections']),
        ];
    }
}
