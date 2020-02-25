<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Text extends Fieldtype
{
    protected $configFields = [
        'placeholder' => [
            'type' => 'text',
            'instructions' => 'Set default placeholder text.',
            'width' => 50,
        ],
        'input_type' => [
            'type' => 'select',
            'default' => 'text',
            'instructions' => 'Set the HTML5 input type.',
            'width' => 50,
            'options' => [
                'color',
                'email',
                'month',
                'number',
                'password',
                'tel',
                'text',
                'time',
                'url',
                'week',
            ]
        ],
        'character_limit' => [
            'type' => 'integer',
            'instructions' => 'Set the maximum number of enterable characters.',
            'width' => 50,
        ],
        'prepend' => [
            'type' => 'text',
            'instructions' => 'Add text before (to the left of) the text input.',
            'width' => 50,
        ],
        'append' => [
            'type' => 'text',
            'instructions' => 'Add text after (to the right of) the text input.',
            'width' => 50,
        ],
    ];
}
