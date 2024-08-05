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
            'keys' => $this->keys()->mapWithKeys(fn ($item) => [$item['key'] => $item['value']]),
        ];
    }

    private function keys()
    {
        return collect($this->config('keys'))->map(fn ($value, $key) => [
            'key' => is_array($value) ? $value['key'] : $key,
            'value' => is_array($value) ? $value['value'] : $value,
        ])->values();
    }

    public function preProcess($data)
    {
        if ($this->isKeyed()) {
            $isMulti = is_array(SupportArr::first($data));

            return $this->keys()->mapWithKeys(function ($item) use ($isMulti, $data) {
                $key = $item['key'];

                $value = $isMulti
                    ? collect($data)->where('key', $key)->pluck('value')->first()
                    : $data[$key] ?? null;

                return [$key => $value];
            })->all();
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
        $isMulti = false;
        foreach (array_keys($data) as $key) {
            if (is_numeric($key)) {
                $isMulti = true;
                break;
            }
        }

        if ($isMulti) {
            return collect($data)
                ->map(fn ($value, $key) => ['key' => $key, 'value' => $value])
                ->values()
                ->all();
        }

        return $data;
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

    public function augment($value)
    {
        if (is_array(SupportArr::first($value))) {
            return collect($value)
                ->mapWithKeys(fn ($item) => [$item['key'] => $item['value']])
                ->all();
        }

        return $value;
    }
}
