<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Color extends Fieldtype
{
    protected $configFields = [
        'swatches' => [
            'type' => 'list',
            'instructions' => 'Pre-define colors that can be selected from a list.',
        ],
        'theme' => [
            'type' => 'select',
            'default' => 'classic',
            'instructions' => 'Choose between the classic (more options) and mini (simpler) color picker.',
            'options' => [
                'classic' => 'Classic',
                'nano' => 'Mini'
            ],
        ],
        'color_modes' => [
            'type' => 'checkboxes',
            'inline' => 'true',
            'instructions' => 'Choose which color modes you want to pick between.',
            'options' => [
                'hex' => 'HEX',
                'rgba' => 'RGBA',
                'hsla' => 'HSLA',
                'hsva' => 'HSVA',
                'cmyk' => 'CMYK'
            ],
            'default' => ['HEXA']
        ],
        'default_color_mode' => [
            'instructions' => 'Set the pre-selected color mode.',
            'type' => 'select',
            'default' => 'HEXA',
            'options' => ['HEXA', 'RGBA', 'HSLA', 'HSVA', 'CMYK'],
        ],
        'lock_opacity' => [
            'instructions' => 'Disables the alpha slider, preventing adjustments to opacity.',
            'type' => 'toggle',
            'default' => false
        ]
    ];
}
