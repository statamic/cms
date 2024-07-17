<?php

namespace Statamic\Fieldtypes;

use Closure;
use Statamic\Facades\GraphQL;
use Statamic\Fields\Fieldtype;
use Statamic\GraphQL\Types\ArrayType;
use Statamic\Support\Arr as SupportArr;

class Arr extends Fieldtype
{
    protected $categories = ['structured'];
    protected static $handle = 'array';

    protected function configFieldItems(): array
    {
        return [
            [
                'display' => __('Appearance & Behavior'),
                'fields' => [
                    'mode' => [
                        'display' => __('UI Mode'),
                        'instructions' => __('statamic::fieldtypes.array.config.mode'),
                        'type' => 'radio',
                        'default' => 'dynamic',
                        'options' => [
                            'dynamic' => __('Dynamic'),
                            'keyed' => __('Keyed'),
                            'single' => __('Single'),
                        ],
                    ],
                    'keys' => [
                        'display' => __('Keys'),
                        'instructions' => __('statamic::fieldtypes.array.config.keys'),
                        'type' => 'array',
                        'key_header' => __('Key'),
                        'value_header' => __('Label').' ('.__('Optional').')',
                        'add_button' => __('Add Key'),
                        'unless' => [
                            'mode' => 'dynamic',
                        ],
                    ],
                ],
            ],
        ];
    }

    public function preload(): array
    {
        return [
            'keys' => collect($this->config('keys'))
                ->mapWithKeys(function ($value, $index) {
                    $key = is_array($value) ? $value['key'] : $index;
                    $label = is_array($value) ? $value['value'] : $value;

                    return [$key => $label];
                }),
        ];
    }

    public function preProcess($data)
    {
        if ($this->isKeyed()) {
            return collect($this->config('keys'))
                ->mapWithKeys(function ($value, $index) use ($data) {
                    $key = is_array($value) ? $value['key'] : $index;
                    $value = collect($data)->where('key', $key)->pluck('value')->first();

                    return [$key => $value ?? null];
                })
                ->all();
        }

        // When using the legacy format, return the data as is.
        if (! is_array(SupportArr::first($data))) {
            return $data ?? [];
        }

        return collect($data)
            ->mapWithKeys(fn ($item) => [
                (string) $item['key'] => $item['value'],
            ])
            ->all();
    }

    public function preProcessConfig($data)
    {
        return $data ?? [];
    }

    public function process($data)
    {
        return collect($data)
            ->map(fn ($value, $key) => [
                'key' => $key,
                'value' => $value,
            ])
            ->values()
            ->all();
    }

    protected function isKeyed()
    {
        return (bool) $this->config('keys');
    }

    public function toGqlType()
    {
        return GraphQL::type(ArrayType::NAME);
    }

    public function rules(): array
    {
        if ($this->isKeyed()) {
            return [];
        }

        return [function ($handle, $value, Closure $fail) {
            $values = collect($value);

            if ($values->has('null')) {
                $fail('statamic::validation.arr_fieldtype')->translate();
            }

            if ($values->count() !== $values->reject(fn ($v) => is_null($v))->count()) {
                $fail('statamic::validation.arr_fieldtype')->translate();
            }
        }];
    }
}
