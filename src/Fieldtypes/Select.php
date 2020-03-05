<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;
use Statamic\Fields\LabeledValue;
use Statamic\Support\Arr;

class Select extends Fieldtype
{
    protected $configFields = [
        'placeholder' => [
            'type' => 'text',
            'default' => '',
            'instructions' => 'Set default, non-selectable placeholder text.',
            'width' => 50,
        ],
        'options' => [
            'type' => 'array',
            'key_header' => 'Key',
            'value_header' => 'Label',
            'instructions' => 'Set the keys and their optional labels.',
            'add_button' => 'Add Option',
        ],
        'clearable' => [
            'type' => 'toggle',
            'default' => false,
            'instructions' => 'Enable to allow deselecting your option.',
            'width' => 50,
        ],
        'multiple' => [
            'type' => 'toggle',
            'default' => false,
            'instructions' => 'Allow multiple selections.',
            'width' => 50,
        ],
        'searchable' => [
            'type' => 'toggle',
            'default' => true,
            'instructions' => 'Enable searching through possible options.',
            'width' => 50,
        ],
        'taggable' => [
            'type' => 'toggle',
            'default' => false,
            'display' => 'Allow additions',
            'instructions' => 'Allow adding new options in addition to pre-defined options',
            'width' => 50,
        ],
        'push_tags' => [
            'type' => 'toggle',
            'default' => false,
            'instructions' => 'Add newly created tags to the options list.',
            'width' => 50,
        ],
        'cast_booleans' => [
            'type' => 'toggle',
            'default' => false,
            'instructions' => 'Options with values of true and false will be saved as booleans.',
            'width' => 50,
        ]
    ];

    protected $indexComponent = 'tags';

    public function preProcessIndex($value)
    {
        if (! $value) {
            return [];
        }

        return collect(Arr::wrap($value))->map(function ($value) {
            return array_get($this->field->get('options'), $value, $value);
        })->all();
    }

    public function augment($value)
    {
        $label = is_null($value) ? null : array_get($this->config('options'), $value, $value);

        return new LabeledValue($value, $label);
    }

    public function preProcess($value)
    {
        if ($this->config('cast_booleans')) {
            if ($value === true) {
                return 'true';
            } elseif ($value === false) {
                return 'false';
            }
        }

        return $value;
    }

    public function process($value)
    {
        if ($this->config('cast_booleans')) {
            if ($value === 'true') {
                return true;
            } elseif ($value === 'false') {
                return false;
            }
        }

        return $value;
    }
}
