<?php

namespace Statamic\Fieldtypes;

use Statamic\Dictionaries\Dictionary as DictionaryInstance;
use Statamic\Dictionaries\Item;
use Statamic\Exceptions\DictionaryNotFoundException;
use Statamic\Exceptions\UndefinedDictionaryException;
use Statamic\Facades\Dictionary as Dictionaries;
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

    public function preProcessIndex($data)
    {
        return collect($this->getItemData($data))->pluck('label')->all();
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
            'multiple' => $this->multiple(),
            'options' => $this->dictionary()->options(),
        ];
    }

    protected function multiple(): bool
    {
        return $this->config('max_items') !== 1;
    }

    public function dictionary(): DictionaryInstance
    {
        $config = is_array($config = $this->config('dictionary')) ? $config : ['type' => $config];

        if (! $handle = Arr::pull($config, 'type')) {
            throw new UndefinedDictionaryException;
        }

        if ($dictionary = Dictionaries::find($handle, $config)) {
            return $dictionary;
        }

        throw new DictionaryNotFoundException($handle);
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

    public function keywords(): array
    {
        return \Statamic\Facades\Dictionary::all()
            ->flatMap(fn ($dictionary) => [
                str($dictionary->handle())->replace('_', ' ')->toString(),
                ...$dictionary->keywords(),
            ])
            ->merge(['select', 'option', 'choice', 'dropdown', 'list'])
            ->unique()->values()->all();
    }
}
