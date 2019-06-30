<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Radio extends Fieldtype
{
    protected $configFields = [
        'options' => [
            'type' => 'array',
            'value_header' => 'Label'
        ],
        'inline' => ['type' => 'toggle']
    ];
}
