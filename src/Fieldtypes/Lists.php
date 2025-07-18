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
            'cast_integers' => [
                'display' => __('Cast Integers'),
                'instructions' => __('statamic::fieldtypes.list.config.cast_integers'),
                'type' => 'toggle',
                'default' => false,
            ],
            'default' => [
                'display' => __('Default Value'),
                'instructions' => __('statamic::messages.fields_default_instructions'),
                'type' => 'list',
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

        return collect($data)
            ->reject(fn ($value) => in_array($value, [null, ''], true))
            ->when($this->config('cast_integers'), function ($collection) {
                return $collection->map(fn ($value) => (int) $value);
            })
            ->values()
            ->all();
    }

    public function toGqlType()
    {
        return GraphQL::listOf(GraphQL::string());
    }
}
