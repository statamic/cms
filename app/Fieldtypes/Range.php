<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Range extends Fieldtype
{
    protected $configFields = [
        'min' => [
            'type' => 'integer',
            'default' => 0,
            'width' => 33
        ],
        'max' => [
            'type' => 'integer',
            'default' => 100,
            'width' => 33
        ],
        'step' => [
            'type' => 'integer',
            'default' => 1,
            'width' => 33
        ],
        'prepend' => ['type' => 'text', 'width' => 50],
        'append' => ['type' => 'text', 'width' => 50]
    ];
}