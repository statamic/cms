<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Color extends Fieldtype
{
    protected $categories = ['special'];
    protected $keywords = ['rgb', 'hex', 'colour'];

    protected function configFieldItems(): array
    {
        return [
            [
                'display' => __('Selection & Options'),
                'fields' => [
                    'swatches' => [
                        'display' => __('Swatches'),
                        'instructions' => __('statamic::fieldtypes.color.config.swatches'),
                        'type' => 'list',
                        'add_button' => __('Add Color'),
                    ],
                ],
            ],
            [
                'display' => __('Appearance'),
                'fields' => [
                    'allow_any' => [
                        'display' => __('Allow Any Color'),
                        'instructions' => __('statamic::fieldtypes.color.config.allow_any'),
                        'type' => 'toggle',
                        'default' => true,
                    ],
                ],
            ],
            [
                'display' => __('Data & Format'),
                'fields' => [
                    'default' => [
                        'display' => __('Default Color'),
                        'instructions' => __('statamic::fieldtypes.color.config.default'),
                        'type' => 'color',
                    ],
                ],
            ],
        ];
    }
}
