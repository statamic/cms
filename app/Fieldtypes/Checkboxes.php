<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Checkboxes extends Fieldtype
{
    protected $configFields = [
        'options' => [
            'type' => 'array',
            'value_header' => 'Label',
            'instructions' => 'Set the array keys and their optional labels.'
        ],
        'inline' => [
            'type' => 'toggle',
            'instructions' => 'Show the checkboxes in a row.'
        ]
    ];
}
