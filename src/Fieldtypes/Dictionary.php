<?php

namespace Statamic\Fieldtypes;

use mysql_xdevapi\Collection;
use Statamic\Fields\Fieldtype;

class Dictionary extends Fieldtype
{
    protected $categories = ['relationship'];
    protected $selectableInForms = true; // TODO: include in frontend forms
    protected $indexComponent = 'tags';

    protected function configFieldItems(): array
    {
        return [
            [
                'display' => __('Options'),
                'fields' => [
                    'dictionary' => [
                        'display' => __('Dictionary'),
                        'instructions' => 'Which dictionary do you want to select options from?', // TODO: move into translations file
                        'type' => 'select',
                        'options' => \Statamic\Facades\Dictionary::all()
                            ->mapWithKeys(fn ($dictionary) => [$dictionary->handle() => $dictionary->title()])
                            ->all(),
                        'taggable' => true,
                        'validate' => 'required',
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
                    'multiple' => [
                        'display' => __('Multiple'),
                        'instructions' => __('statamic::fieldtypes.select.config.multiple'),
                        'type' => 'toggle',
                        'default' => false,
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
            'selectedOptions' => collect($this->dictionary()->options())
                ->only($this->field->value())
                ->map(fn ($label, $value) => ['value' => $value, 'label' => $label])
                ->values()
                ->all(),
        ];
    }

    public function augment($value)
    {
        if ($this->multiple() && is_null($value)) {
            return [];
        }

        $dictionary = \Statamic\Facades\Dictionary::find($this->config('dictionary'));

        if ($this->multiple()) {
            return collect($value)->map(function ($value) use ($dictionary) {
                return $dictionary->get($value);
            })->all();
        }

        return $dictionary->get($value);
    }

    protected function multiple(): bool
    {
        return $this->config('multiple');
    }

    private function dictionary(): \Statamic\Dictionaries\Dictionary
    {
        return \Statamic\Facades\Dictionary::find($this->config('dictionary'));
    }

    // TODO: graphql - how can we make it work since the keys will be dynamic?
}
