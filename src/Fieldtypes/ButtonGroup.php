<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

class ButtonGroup extends Fieldtype
{
    use HasSelectOptions;

    protected $categories = ['controls'];
    protected $indexComponent = 'tags';

    protected function configFieldItems(): array
    {
        return [
            [
                'display' => __('Options'),
                'fields' => [
                    'options' => [
                        'display' => __('Options'),
                        'instructions' => __('statamic::fieldtypes.radio.config.options'),
                        'type' => 'array',
                        'value_header' => __('Label').' ('.__('Optional').')',
                        'add_button' => __('Add Option'),
                    ],
                    'default' => [
                        'display' => __('Default Value'),
                        'instructions' => __('statamic::messages.fields_default_instructions'),
                        'type' => 'text',
                    ],
                ],
            ],
        ];
    }
}
