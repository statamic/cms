<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Select extends Fieldtype
{
    protected $configFields = [
        'options' => [
            'type' => 'array',
            'field' => [
                'key_header' => 'Value',
                'value_header' => 'Label'
            ]
        ],
        'placeholder' => [
            'type' => 'text',
            'default' => ''
        ],
        'clearable' => [
            'type' => 'toggle',
            'default' => false
        ],
        'multiple' => [
            'type' => 'toggle',
            'default' => false
        ],
        'searchable' => [
            'type' => 'toggle',
            'default' => true
        ],
        'taggable' => [
            'type' => 'toggle',
            'default' => false
        ],
        'push_tags' => [
            'type' => 'toggle',
            'default' => false
        ],
    ];
}
