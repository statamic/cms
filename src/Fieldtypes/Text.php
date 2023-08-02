<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;
use Statamic\Support\Str;

class Text extends Fieldtype
{
    protected $categories = ['text'];
    protected $selectableInForms = true;

    protected function configFieldItems(): array
    {
        return [
            [
                'display' => __('Input Behavior'),
                'fields' => [
                    'input_type' => [
                        'display' => __('Input Type'),
                        'instructions' => __('statamic::fieldtypes.text.config.input_type'),
                        'type' => 'select',
                        'default' => 'text',
                        'options' => [
                            'color',
                            'date',
                            'email',
                            'hidden',
                            'month',
                            'number',
                            'password',
                            'tel',
                            'text',
                            'time',
                            'url',
                            'week',
                        ],
                    ],
                    'placeholder' => [
                        'display' => __('Placeholder'),
                        'instructions' => __('statamic::fieldtypes.text.config.placeholder'),
                        'type' => 'text',
                    ],
                    'default' => [
                        'display' => __('Default Value'),
                        'instructions' => __('statamic::messages.fields_default_instructions'),
                        'type' => 'text',
                    ],
                    'character_limit' => [
                        'display' => __('Character Limit'),
                        'instructions' => __('statamic::fieldtypes.text.config.character_limit'),
                        'type' => 'integer',
                    ],
                ],
            ],
            [
                'display' => __('Appearance'),
                'fields' => [
                    'prepend' => [
                        'display' => __('Prepend'),
                        'instructions' => __('statamic::fieldtypes.text.config.prepend'),
                        'type' => 'text',
                    ],
                    'append' => [
                        'display' => __('Append'),
                        'instructions' => __('statamic::fieldtypes.text.config.append'),
                        'type' => 'text',
                    ],
                ],
            ],
            [
                'display' => 'Antlers',
                'fields' => [
                    'antlers' => [
                        'display' => __('Allow Antlers'),
                        'instructions' => __('statamic::fieldtypes.any.config.antlers'),
                        'type' => 'toggle',
                    ],
                ],
            ],
        ];
    }

    public function process($data)
    {
        if ($data !== null && $this->config('input_type') === 'number') {
            return Str::contains($data, '.') ? (float) $data : (int) $data;
        }

        return $data;
    }

    public function preProcessIndex($value)
    {
        if ($value) {
            return $this->config('prepend').$value.$this->config('append');
        }
    }
}
