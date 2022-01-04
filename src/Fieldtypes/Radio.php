<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Radio extends Fieldtype
{
    use HasSelectOptions;

    protected $categories = ['controls'];
    protected $selectableInForms = true;
    protected $indexComponent = 'tags';

    protected function configFieldItems(): array
    {
        return [
            'options' => [
                'display' => __('Options'),
                'instructions' => __('statamic::fieldtypes.radio.config.options'),
                'type' => 'array',
                'value_header' => __('Label').' ('.__('Optional').')',
            ],
            'inline' => [
                'display' => __('Inline'),
                'instructions' => __('statamic::fieldtypes.radio.config.inline'),
                'type' => 'toggle',
                'width' => 50,
            ],
            'cast_booleans' => [
                'display' => __('Cast Booleans'),
                'instructions' => __('statamic::fieldtypes.any.config.cast_booleans'),
                'type' => 'toggle',
                'default' => false,
                'width' => 50,
            ],
            'default' => [
                'display' => __('Default Value'),
                'instructions' => __('statamic::messages.fields_default_instructions'),
                'type' => 'text',
                'width' => 50,
            ],
        ];
    }
}
