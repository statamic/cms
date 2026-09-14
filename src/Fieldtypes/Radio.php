<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;
use Statamic\Fieldtypes\Concerns\MigratesLegacyInlineConfig;

use function Statamic\trans as __;

class Radio extends Fieldtype
{
    use HasSelectOptions;
    use MigratesLegacyInlineConfig;

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
                    'appearance' => [
                        'display' => __('Appearance'),
                        'instructions' => __('statamic::fieldtypes.radio.config.appearance'),
                        'type' => 'control_appearance',
                        'default' => 'default',
                        'control' => 'radio',
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
