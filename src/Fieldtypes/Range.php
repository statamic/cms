<?php

namespace Statamic\Fieldtypes;

use Statamic\Facades\GraphQL;
use Statamic\Fields\Fieldtype;

class Range extends Fieldtype
{
    protected $categories = ['controls'];

    protected function configFieldItems(): array
    {
        return [
            [
                'display' => __('Input Behavior'),
                'fields' => [
                    'min' => [
                        'display' => __('Min'),
                        'instructions' => __('statamic::fieldtypes.range.config.min'),
                        'type' => 'text',
                        'input_type' => 'number',
                        'default' => 0,
                        'width' => 50,
                    ],
                    'max' => [
                        'display' => __('Max'),
                        'instructions' => __('statamic::fieldtypes.range.config.max'),
                        'type' => 'text',
                        'input_type' => 'number',
                        'default' => 100,
                        'width' => 50,
                    ],
                    'step' => [
                        'display' => __('Step'),
                        'instructions' => __('statamic::fieldtypes.range.config.step'),
                        'type' => 'text',
                        'input_type' => 'number',
                        'default' => 1,
                        'width' => 50,
                    ],
                ],
            ],
            [
                'display' => __('Appearance'),
                'fields' => [
                    'prepend' => [
                        'display' => __('Prepend'),
                        'instructions' => __('statamic::fieldtypes.range.config.prepend'),
                        'type' => 'text',
                        'width' => 50,
                    ],
                    'append' => [
                        'display' => __('Append'),
                        'instructions' => __('statamic::fieldtypes.range.config.append'),
                        'type' => 'text',
                        'width' => 50,
                    ],
                ],
            ],
            [
                'display' => __('Data & Format'),
                'fields' => [
                    'default' => [
                        'display' => __('Default Value'),
                        'instructions' => __('statamic::messages.fields_default_instructions'),
                        'type' => 'text',
                        'input_type' => 'number',
                        'default' => null,
                    ],
                ],
            ],
        ];
    }

    public function process($data)
    {
        if ($this->usesDecimals()) {
            return (float) $data;
        }

        return (int) $data;
    }

    protected function usesDecimals(): bool
    {
        $step = $this->config('step', 1);
        $min = $this->config('min', 0);
        $max = $this->config('max', 100);

        return $this->isDecimal($step) || $this->isDecimal($min) || $this->isDecimal($max);
    }

    protected function isDecimal($value): bool
    {
        if (! is_numeric($value)) {
            return false;
        }

        return floor((float) $value) != (float) $value;
    }

    public function toGqlType()
    {
        return $this->usesDecimals() ? GraphQL::float() : GraphQL::int();
    }
}
