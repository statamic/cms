<?php

namespace Statamic\Fieldtypes;

use Statamic\Facades\GraphQL;
use Statamic\Fields\Fieldtype;

class Taggable extends Fieldtype
{
    protected $categories = ['structured'];
    protected $component = 'tags';
    protected $icon = 'tags';

    protected function configFieldItems(): array
    {
        return [
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
            'select_on_commas' => [
                'display' => __('Select on Commas'),
                'instructions' => __('statamic::fieldtypes.taggable.config.select_on_commas'),
                'type' => 'toggle',
                'default' => true,
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
        return ($data) ? $data : [];
    }

    public function toGqlType()
    {
        return GraphQL::listOf(GraphQL::string());
    }
}
