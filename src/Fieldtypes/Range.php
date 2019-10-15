<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Range extends Fieldtype
{
    protected $configFields = [
        'min' => [
            'type' => 'integer',
            'default' => 0,
            'width' => 33,
            'instructions' => 'The minimum, left-most value.'
        ],
        'max' => [
            'type' => 'integer',
            'default' => 100,
            'width' => 33,
            'instructions' => 'The maximum, right-most value.'
        ],
        'step' => [
            'type' => 'integer',
            'default' => 1,
            'width' => 33,
            'instructions' => 'The minimum size between values.'
        ],
        'prepend' => [
            'type' => 'text',
            'width' => 50,
            'instructions' => 'Add text to the beginning (left-side) of the slider.'
        ],
        'append' => [
            'type' => 'text',
            'width' => 50,
            'instructions' => 'Add text to the end (right-side) of the slider.'
        ]
    ];
}
