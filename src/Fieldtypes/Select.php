<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Select extends Fieldtype
{
    protected $configFields = [
        'options' => [
            'type' => 'array',
            'key_header' => 'Key',
            'value_header' => 'Label',
            'instructions' => 'Set the keys and their optional labels.',
            'add_button' => 'Add Option'
        ],
        'placeholder' => [
            'type' => 'text',
            'default' => '',
            'instructions' => 'Set default, non-selectable placeholder text.'
        ],
        'clearable' => [
            'type' => 'toggle',
            'default' => false,
            'instructions' => 'Enable to allow deselecting your option.'
        ],
        'multiple' => [
            'type' => 'toggle',
            'default' => false,
            'instructions' => 'Allow multiple selections.'
        ],
        'searchable' => [
            'type' => 'toggle',
            'default' => true,
            'instructions' => 'Enable searching through possible options.'
        ],
        'taggable' => [
            'type' => 'toggle',
            'default' => false,
            'instructions' => 'Use a "tag" style interface for multiple selections.'
        ],
        'push_tags' => [
            'type' => 'toggle',
            'default' => false,
            'instructions' => 'Add newly created tags to the options list.'
        ],
    ];

    public function preProcessIndex($value)
    {
        if (! $value) {
            return null;
        }

        return array_get($this->field->get('options'), $value, $value);
    }

    public function augment($value)
    {
        return array_get($this->config('options'), $value, $value);
    }
}
