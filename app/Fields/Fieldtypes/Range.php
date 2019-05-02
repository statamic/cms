<?php

namespace Statamic\Fields\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Range extends Fieldtype
{
    protected $configFields = [
        'min' => [
            'type' => 'integer',
            'default' => 0
        ],
        'max' => [
            'type' => 'integer',
            'default' => 100
        ],
        'step' => [
            'type' => 'integer',
            'default' => 1
        ],
        'prepend' => ['type' => 'text'],
        'append' => ['type' => 'text'],
    ];
}