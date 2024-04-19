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
                'instructions' => __('statamic::fieldtypes.select.config.options'),
                'type' => 'array',
                'key_header' => __('Key'),
                'value_header' => __('Label').' ('.__('Optional').')',
                'add_button' => __('Add Option'),
                'validate' => [function ($attribute, $value, $fail) {
                    $optionsWithoutKeys = collect($value)->keys()->filter(fn ($key) => empty($key) || $key === 'null');

                    if ($optionsWithoutKeys->isNotEmpty()) {
                        $fail(__('statamic::validation.options_require_keys'));
                    }
                }],
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
