<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Width extends Fieldtype
{
    use HasSelectOptions;

    protected $categories = ['controls'];
    protected $indexComponent = 'tags';

    protected function configFieldItems(): array
    {
        return [
            [
                'display' => __('Selection & Options'),
                'fields' => [
                    'options' => [
                        'display' => __('Options'),
                        'instructions' => __('statamic::fieldtypes.width.config.options'),
                        'type' => 'list',
                        'default' => [25, 33, 50, 66, 75, 100],
                    ],
                ],
            ],
            [
                'display' => __('Appearance'),
                'fields' => [
                    'size' => [
                        'display' => __('Size'),
                        'instructions' => __('statamic::fieldtypes.width.config.size'),
                        'type' => 'select',
                        'options' => ['base' => 'Base', 'lg' => 'Large'],
                        'default' => 'lg',
                    ],
                ],
            ],
            [
                'display' => __('Data & Format'),
                'fields' => [
                    'default' => [
                        'display' => __('Default Value'),
                        'instructions' => __('statamic::messages.fields_default_instructions'),
                        'type' => 'text',
                        'default' => 100,
                        'width' => 50,
                    ],
                ],
            ],
        ];
    }
}
