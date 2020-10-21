<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Text extends Fieldtype
{
    protected function configFieldItems(): array
    {
        return [
            'placeholder' => [
                'display' => __('Placeholder'),
                'instructions' => __('statamic::fieldtypes.text.config.placeholder'),
                'type' => 'text',
                'width' => 50,
            ],
            'input_type' => [
                'display' => __('Input Type'),
                'instructions' => __('statamic::fieldtypes.text.config.input_type'),
                'type' => 'select',
                'default' => 'text',
                'width' => 50,
                'options' => [
                    'color',
                    'email',
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
            'character_limit' => [
                'display' => __('Character Limit'),
                'instructions' => __('statamic::fieldtypes.text.config.character_limit'),
                'type' => 'integer',
                'width' => 50,
            ],
            'prepend' => [
                'display' => __('Prepend'),
                'instructions' => __('statamic::fieldtypes.text.config.prepend'),
                'type' => 'text',
                'width' => 50,
            ],
            'append' => [
                'display' => __('Append'),
                'instructions' => __('statamic::fieldtypes.text.config.append'),
                'type' => 'text',
                'width' => 50,
            ],
        ];
    }

    public function process($data)
    {
        if ($this->config('input_type') === 'number') {
            return (int) $data;
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
