<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Color extends Fieldtype
{
    protected $configFields = [
        'swatches' => [
            'type' => 'list'
        ],
        'swatches' => [
            'type' => 'list'
        ],
        'theme' => [
            'type' => 'select',
            'default' => 'classic',
            'options' => [
                'classic' => 'Classic',
                'nano' => 'Mini'
            ],
        ],
        'color_modes' => [
            'type' => 'checkboxes',
            'inline' => 'true',
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
            'type' => 'select',
            'default' => 'HEXA',
            'options' => ['HEXA', 'RGBA', 'HSLA', 'HSVA', 'CMYK'],
        ]
    ];
}
