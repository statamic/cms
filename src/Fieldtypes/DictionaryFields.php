<?php

namespace Statamic\Fieldtypes;

use Statamic\Facades\Dictionary;
use Statamic\Fields\Fields;
use Statamic\Fields\Fieldtype;
use Statamic\Support\Arr;

class DictionaryFields extends Fieldtype
{
    protected $selectable = false;

    public function preload()
    {
        $typeField = new Fields([[
            'handle' => 'type',
            'field' => [
                'display' => __('Dictionary'),
                'instructions' => __('statamic::fieldtypes.dictionary.config.dictionary'),
                'type' => 'select',
                'options' => Dictionary::all()
                    ->mapWithKeys(fn ($dictionary) => [$dictionary->handle() => $dictionary->title()])
                    ->all(),
                'max_items' => 1,
                'validate' => 'required',
            ],
        ]]);

        return [
            'type' => [
                'fields' => $typeField->toPublishArray(),
                'meta' => $typeField->meta()->all(),
            ],
            'dictionaries' => Dictionary::all()->mapWithKeys(function (\Statamic\Dictionaries\Dictionary $dictionary) {
                return [$dictionary->handle() => [
                    'fields' => $dictionary->fields()->toPublishArray(),
                    'meta' => $dictionary->fields()->meta()->all(),
                    'defaults' => $dictionary->fields()->all()->map(function ($field) {
                        return $field->fieldtype()->preProcess($field->defaultValue());
                    })->all(),
                ]];
            })->all(),
        ];
    }

    public function preProcess($data): array
    {
        if (is_null($data)) {
            return ['type' => null];
        }

        if (is_string($data)) {
            $data = ['type' => $data];
        }

        $dictionary = Dictionary::find($data['type']);

        return array_merge(
            ['type' => $data['type']],
            $dictionary->fields()->addValues($data)->preProcess()->values()->all()
        );
    }

    public function process($data): string|array
    {
        $dictionary = Dictionary::find($data['type']);
        $values = $dictionary->fields()->addValues($data)->process()->values();

        if ($values->filter()->isEmpty()) {
            return $dictionary->handle();
        }

        return array_merge(['type' => $dictionary->handle()], $values->all());
    }

    public function extraRules(): array
    {
        if (! $dictionary = Arr::get($this->field->value(), 'type')) {
            return [
                $this->field->handle().'.type' => ['required'],
            ];
        }

        $dictionary = Dictionary::find($dictionary);

        $rules = $dictionary
            ->fields()
            ->addValues((array) $this->field->value())
            ->validator()
            ->withContext([
                'prefix' => $this->field->handle().'.',
            ])
            ->rules();

        return collect($rules)->mapWithKeys(function ($rules, $handle) {
            return [$this->field->handle().'.'.$handle => $rules];
        })->all();
    }
}
