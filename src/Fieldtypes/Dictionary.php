<?php

namespace Statamic\Fieldtypes;

use Statamic\Dictionaries\Item;
use Statamic\Exceptions\DictionaryNotFoundException;
use Statamic\Exceptions\UndefinedDictionaryException;
use Statamic\Facades\GraphQL;
use Statamic\Fields\Fieldtype;
use Statamic\Support\Arr;

class Dictionary extends Fieldtype
{
    protected $categories = ['controls', 'relationship'];
    protected $selectableInForms = true;
    protected $indexComponent = 'tags';

    protected function configFieldItems(): array
    {
        return [
            [
                'display' => __('Options'),
                'fields' => [
                    'dictionary' => [
                        'type' => 'dictionary_fields',
                        'hide_display' => true,
                        'full_width_setting' => true,
                    ],
                ],
            ],
            [
                'display' => __('Selection'),
                'fields' => [
                    'placeholder' => [
                        'display' => __('Placeholder'),
                        'instructions' => __('statamic::fieldtypes.select.config.placeholder'),
                        'type' => 'text',
                        'default' => '',
                    ],
                    'max_items' => [
                        'display' => __('Max Items'),
                        'instructions' => __('statamic::messages.max_items_instructions'),
                        'min' => 1,
                        'type' => 'integer',
                    ],
                    'clearable' => [
                        'display' => __('Clearable'),
                        'instructions' => __('statamic::fieldtypes.select.config.clearable'),
                        'type' => 'toggle',
                        'default' => false,
                    ],
                    'searchable' => [
                        'display' => __('Searchable'),
                        'instructions' => __('statamic::fieldtypes.select.config.searchable'),
                        'type' => 'toggle',
                        'default' => true,
                    ],
                ],
            ],
            [
                'display' => __('Data'),
                'fields' => [
                    'default' => [
                        'display' => __('Default Value'),
                        'instructions' => __('statamic::messages.fields_default_instructions'),
                        'type' => 'text',
                    ],
                ],
            ],
        ];
    }

    public function preload(): array
    {
        return [
            'url' => cp_route('dictionary-fieldtype', $this->dictionary()->handle()),
            'selectedOptions' => $this->getItemData($this->field->value()),
        ];
    }

    private function getItemData($values)
    {
        return collect($values)->map(function ($key) {
            $item = $this->dictionary()->get($key);

            return [
                'value' => $item?->value() ?? $key,
                'label' => $item?->label() ?? $key,
                'invalid' => ! $item,
            ];
        })->values()->all();
    }

    public function augment($value)
    {
        if ($this->multiple() && is_null($value)) {
            return [];
        }

        $dictionary = $this->dictionary();

        if ($this->multiple()) {
            return collect($value)->map(function ($value) use ($dictionary) {
                return $dictionary->get($value);
            })->filter()->all();
        }

        $item = $value ? $dictionary->get($value) : null;

        return $item ?? new Item(null, null, []);
    }

    public function extraRenderableFieldData(): array
    {
        return [
            'options' => $this->dictionary()->options(),
        ];
    }

    protected function multiple(): bool
    {
        return $this->config('max_items') !== 1;
    }

    public function dictionary(): \Statamic\Dictionaries\Dictionary
    {
        if (! $this->config('dictionary')) {
            throw new UndefinedDictionaryException();
        }

        $config = $this->config('dictionary');

        $handle = is_array($config) ? Arr::get($config, 'type') : $config;
        $context = is_array($config) ? Arr::except($config, 'type') : [];

        $dictionary = \Statamic\Facades\Dictionary::find($handle, $context);

        if (! $dictionary) {
            throw new DictionaryNotFoundException($handle);
        }

        return $dictionary;
    }

    public function toGqlType()
    {
        $type = GraphQL::type($this->dictionary()->getGqlType()->name);

        return $this->multiple()
            ? $this->multiSelectGqlType($type)
            : $this->singleSelectGqlType($type);
    }

    private function singleSelectGqlType($type)
    {
        return [
            'type' => $type,
            'resolve' => function ($item, $args, $context, $info) {
                $resolved = $item->resolveGqlValue($info->fieldName);

                return is_null($resolved->value()) ? null : $resolved;
            },
        ];
    }

    private function multiSelectGqlType($type)
    {
        return [
            'type' => GraphQL::listOf($type),
            'resolve' => function ($item, $args, $context, $info) {
                $resolved = $item->resolveGqlValue($info->fieldName);

                return empty($resolved) ? null : $resolved;
            },
        ];
    }

    public function addGqlTypes()
    {
        GraphQL::addType($this->dictionary()->getGqlType());
    }
}
