<?php

namespace Statamic\Fieldtypes;

use Statamic\Facades\GraphQL;
use Statamic\Fields\Fieldtype;

use function Statamic\trans as __;

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
            ],
            'add_row' => [
                'display' => __('Add Row Label'),
                'instructions' => __('statamic::fieldtypes.list.config.add_row'),
                'type' => 'text',
                'placeholder' => __('Add Item'),
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
        })->map(function ($item) {
            return is_numeric($item)
                ? (str_contains($item, '.') ? (float) $item : (int) $item)
                : $item;
        })->values()->all();
    }

    public function toGqlType()
    {
        return GraphQL::listOf(GraphQL::string());
    }
}
