<?php

namespace Statamic\Fieldtypes;

use Statamic\Facades\GraphQL;
use Statamic\Fields\Fieldtype;
use Statamic\Support\Arr;

class Taggable extends Fieldtype
{
    protected $categories = ['structured'];
    protected $component = 'tags';

    protected function configFieldItems(): array
    {
        return [
            [
                'display' => __('Selection & Options'),
                'fields' => [
                    'placeholder' => [
                        'display' => __('Placeholder'),
                        'instructions' => __('statamic::fieldtypes.select.config.placeholder'),
                        'type' => 'text',
                        'default' => __('statamic::fieldtypes.taggable.config.placeholder'),
                    ],
                    'options' => [
                        'display' => __('Options'),
                        'instructions' => __('statamic::fieldtypes.taggable.config.options'),
                        'type' => 'list',
                        'add_button' => __('Add Option'),
                    ],
                ],
            ],
        ];
    }

    public function preload()
    {
        return [
            'options' => $this->config('options', []),
        ];
    }

    public function preProcess($data)
    {
        return Arr::wrap($data);
    }

    public function toGqlType()
    {
        return GraphQL::listOf(GraphQL::string());
    }
}
