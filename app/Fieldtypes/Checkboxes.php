<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Checkboxes extends Fieldtype
{
    protected $configFields = [
        'options' => [
            'type' => 'array',
        ],
        'inline' => ['type' => 'toggle']
    ];
}
