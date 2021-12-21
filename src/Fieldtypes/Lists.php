<?php

namespace Statamic\Fieldtypes;

use Statamic\Facades\GraphQL;
use Statamic\Fields\Fieldtype;

class Lists extends Fieldtype
{
    protected $categories = ['structured'];
    protected static $handle = 'list';

    protected function configFieldItems(): array
    {
        return [
            'default' => [
                'display' => __('Default Value'),
                'instructions' => __('statamic::messages.fields_default_instructions'),
                'type' => 'list',
                'width' => 50,
            ],
        ];
    }

    public function preProcess($data)
    {
        if (is_null($data)) {
            return [];
        }

        return (array) $data;
    }

    public function process($data)
    {
        if (! is_array($data)) {
            return $data;
        }

        return collect($data)->reject(function ($item) {
            return in_array($item, [null, ''], true);
        })->values()->all();
    }

    public function toGqlType()
    {
        return GraphQL::listOf(GraphQL::string());
    }
}
