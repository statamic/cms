<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Color extends Fieldtype
{
    protected $categories = ['special'];

    protected function configFieldItems(): array
    {
        return [
            [
                'display' => __('Appearance & Behavior'),
                'fields' => [
                    'swatches' => [
                        'display' => __('Swatches'),
                        'instructions' => __('statamic::fieldtypes.color.config.swatches'),
                        'type' => 'list',
                        'add_button' => __('Add Color'),
                    ],
                    'theme' => [
                        'display' => __('Theme'),
                        'instructions' => __('statamic::fieldtypes.color.config.theme'),
                        'type' => 'select',
                        'default' => 'classic',
                        'options' => [
                            'classic' => __('Classic'),
                            'nano' => __('Mini'),
                        ],
                    ],
                    'lock_opacity' => [
                        'display' => __('Lock Opacity'),
                        'instructions' => __('statamic::fieldtypes.color.config.lock_opacity'),
                        'type' => 'toggle',
                        'default' => false,
                    ],
                    'default_color_mode' => [
                        'display' => __('Default Color Mode'),
                        'instructions' => __('statamic::fieldtypes.color.config.default_color_mode'),
                        'type' => 'select',
                        'options' => ['HEXA', 'RGBA', 'HSLA', 'HSVA', 'CMYK'],
                        'default' => 'HEXA',
                    ],
                    'color_modes' => [
                        'display' => __('Color Modes'),
                        'instructions' => __('statamic::fieldtypes.color.config.color_modes'),
                        'type' => 'checkboxes',
                        'inline' => 'true',
                        'options' => [
                            'hex' => 'HEXA',
                            'rgba' => 'RGBA',
                            'hsla' => 'HSLA',
                            'hsva' => 'HSVA',
                            'cmyk' => 'CMYK',
                        ],
                        'default' => 'hex',
                    ],
                ],
            ],
        ];
    }
}
