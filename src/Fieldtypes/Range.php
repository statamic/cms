<?php

namespace Statamic\Fieldtypes;

use Statamic\Facades\GraphQL;
use Statamic\Fields\Fieldtype;

class Range extends Fieldtype
{
    protected $categories = ['controls'];

    protected function configFieldItems(): array
    {
        return [
            [
                'display' => __('Behavior'),
                'fields' => [
                    'min' => [
                        'display' => __('Min'),
                        'instructions' => __('statamic::fieldtypes.range.config.min'),
                        'type' => 'integer',
                        'default' => 0,
                    ],
                    'max' => [
                        'display' => __('Max'),
                        'instructions' => __('statamic::fieldtypes.range.config.max'),
                        'type' => 'integer',
                        'default' => 100,
                    ],
                    'step' => [
                        'display' => __('Step'),
                        'instructions' => __('statamic::fieldtypes.range.config.step'),
                        'type' => 'integer',
                        'default' => 1,
                    ],
                    'default' => [
                        'display' => __('Default Value'),
                        'instructions' => __('statamic::messages.fields_default_instructions'),
                        'type' => 'text',
                        'input_type' => 'number',
                        'default' => null,
                    ],
                ],
            ],
            [
                'display' => __('Appearance'),
                'fields' => [
                    'prepend' => [
                        'display' => __('Prepend'),
                        'instructions' => __('statamic::fieldtypes.range.config.prepend'),
                        'type' => 'text',
                    ],
                    'append' => [
                        'display' => __('Append'),
                        'instructions' => __('statamic::fieldtypes.range.config.append'),
                        'type' => 'text',
                    ],
                ],
            ],
        ];
    }

    public function process($data)
    {
        return (int) $data;
    }

    public function toGqlType()
    {
        return GraphQL::int();
    }
}
