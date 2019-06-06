<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Color extends Fieldtype
{
    protected $configFields = [
        'swatches' => [
            'type' => 'list'
        ],
        'default_mode' => [
            'type' => 'select',
            'default' => 'HEXA',
            'options' => ['HEXA', 'RGBA', 'HSLA', 'HSVA', 'CMYK'],
        ]
    ];
}
