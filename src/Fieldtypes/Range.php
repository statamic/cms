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
            'hidden' => [
                'type' => 'hidden',
                'width' => 50,
            ],
            'min' => [
                'display' => __('Min'),
                'instructions' => __('statamic::fieldtypes.range.config.min'),
                'type' => 'integer',
                'default' => 0,
                'width' => 50,
            ],
            'max' => [
                'display' => __('Max'),
                'instructions' => __('statamic::fieldtypes.range.config.max'),
                'type' => 'integer',
                'default' => 100,
                'width' => 50,
            ],
            'step' => [
                'display' => __('Step'),
                'instructions' => __('statamic::fieldtypes.range.config.step'),
                'type' => 'integer',
                'default' => 1,
                'width' => 50,
            ],
            'default' => [
                'display' => __('Default Value'),
                'instructions' => __('statamic::messages.fields_default_instructions'),
                'type' => 'text',
                'input_type' => 'number',
                'default' => null,
                'width' => 50,
            ],
            'prepend' => [
                'display' => __('Prepend'),
                'instructions' => __('statamic::fieldtypes.range.config.prepend'),
                'type' => 'text',
                'width' => 50,
            ],
            'append' => [
                'display' => __('Append'),
                'instructions' => __('statamic::fieldtypes.range.config.append'),
                'type' => 'text',
                'width' => 50,
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
