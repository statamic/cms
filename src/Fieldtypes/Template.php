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
    ];

    public function queryOperators(): array
    {
        return [
            '=' => __('Is'),
        ];
    }
}
