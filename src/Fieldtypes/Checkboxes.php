<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Checkboxes extends Fieldtype
{
    protected $configFields = [
        'options' => [
            'type' => 'array',
            'value_header' => 'Label',
            'instructions' => 'Set the array keys and their optional labels.'
        ],
        'inline' => [
            'type' => 'toggle',
            'instructions' => 'Show the checkboxes in a row.'
        ]
    ];

    public function augment($values)
    {
        if (is_null($values)) {
            return [];
        }

        return collect($values)->map(function ($value) {
            return [
                'key' => $value,
                'value' => $value,
                'label' => array_get($this->config('options'), $value, $value)
            ];
        })->all();
    }
}
