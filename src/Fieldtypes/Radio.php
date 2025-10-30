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
            [
                'display' => __('Selection & Options'),
                'fields' => [
                    'options' => [
                        'display' => __('Options'),
                        'instructions' => __('statamic::fieldtypes.radio.config.options'),
                        'type' => 'array',
                        'expand' => true,
                        'field' => [
                            'type' => 'text',
                        ],
                    ],
                ],
            ],
            [
                'display' => __('Appearance'),
                'fields' => [
                    'inline' => [
                        'display' => __('Inline'),
                        'instructions' => __('statamic::fieldtypes.radio.config.inline'),
                        'type' => 'toggle',
                    ],
                ],
            ],
            [
                'display' => __('Data & Format'),
                'fields' => [
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
                ],
            ],
        ];
    }
}
