<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Checkboxes extends Fieldtype
{
    use HasSelectOptions;

    protected $categories = ['controls'];
    protected $selectableInForms = true;
    protected $indexComponent = 'tags';

    protected function configFieldItems(): array
    {
        return [
            'inline' => [
                'display' => __('Inline'),
                'instructions' => __('statamic::fieldtypes.checkboxes.config.inline'),
                'type' => 'toggle',
                'width' => 50,
            ],
            'options' => [
                'display' => __('Options'),
                'instructions' => __('statamic::fieldtypes.checkboxes.config.options'),
                'type' => 'array',
                'key_header' => __('Key'),
                'value_header' => __('Label').' ('.__('Optional').')',
            ],
            'default' => [
                'display' => __('Default Value'),
                'instructions' => __('statamic::messages.fields_default_instructions'),
                'type' => 'text',
                'width' => 50,
            ],
        ];
    }

    protected function multiple()
    {
        return true;
    }
}
