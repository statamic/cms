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
                'display' => __('Radio Options'),
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
                'display' => __('Appearance & Behavior'),
                'fields' => [
                    'inline' => [
                        'display' => __('Inline'),
                        'instructions' => __('statamic::fieldtypes.radio.config.inline'),
                        'type' => 'toggle',
                    ],
                    'cast_booleans' => [
                        'display' => __('Cast Booleans'),
                        'instructions' => __('statamic::fieldtypes.any.config.cast_booleans'),
                        'type' => 'toggle',
                        'default' => false,
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
