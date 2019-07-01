<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Radio extends Fieldtype
{
    protected $configFields = [
        'options' => [
            'type' => 'array',
            'value_header' => 'Label',
            'instructions' => 'Set the array keys and their optional labels.'
        ],
        'inline' => [
            'type' => 'toggle',
            'instructions' => 'Show the radio buttons in a row.'
        ]
    ];
}
