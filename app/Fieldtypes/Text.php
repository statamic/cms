<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Text extends Fieldtype
{
    protected $configFields = [
        'placeholder' => [
            'type' => 'text',
            'instructions' => 'Set default placeholder text.'
        ],
        'character_limit' => [
            'type' => 'integer',
            'instructions' => 'Set the maximum number of enterable characters.'
        ],
        'prepend' => [
            'type' => 'text',
            'instructions' => 'Add text before (to the left of) the text input.'
        ],
        'append' => [
            'type' => 'text',
            'instructions' => 'Add text after (to the right of) the text input.'
        ],
    ];
}