<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;
use Statamic\Fields\LabeledValue;

class Radio extends Fieldtype
{
    protected $configFields = [
        'options' => [
            'type' => 'array',
            'value_header' => 'Label',
            'instructions' => 'Set the array keys and their optional labels.'
        ],
        'inline' => [
            'type' => 'toggle',
            'instructions' => 'Show the radio buttons in a row.'
        ]
    ];

    public function augment($value)
    {
        return new LabeledValue($value, array_get($this->config('options'), $value, $value));
    }
}
