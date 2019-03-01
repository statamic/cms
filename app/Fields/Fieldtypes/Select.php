<?php

namespace Statamic\Fields\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Select extends Fieldtype
{
    protected $configFields = [
        'options' => [
            'type' => 'array',
        ]
    ];
}
