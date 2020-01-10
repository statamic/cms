<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Template extends Fieldtype
{
    protected $configFields = [
        'hide_partials' => [
            'type' => 'toggle',
            'default' => true,
            'width' => 50,
            'instructions' => 'Partials are rarely intended to be used as templates.'
        ],
        'hide_error_templates' => [
            'type' => 'toggle',
            'default' => true,
            'width' => 50,
            'instructions' => 'Error templates are intended to be a last resort, not first pick.'
        ],
    ];
}
