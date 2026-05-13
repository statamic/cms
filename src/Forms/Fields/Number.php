<?php

namespace Statamic\Forms\Fields;

use Statamic\Support\Arr;

class Number extends FormFieldtype
{
    protected static $fieldtype = 'integer';
    protected $description = 'Collects a number. You can set minimum and maximum values.';
    protected $icon = 'number';
    protected $categories = ['number'];

    public function configFieldItems(): array
    {
        return [
            'min' => [
                'display' => __('Min'),
                'instructions' => __('statamic::fieldtypes.integer.config.min'),
                'type' => 'integer',
                'width' => '50',
            ],
            'max' => [
                'display' => __('Max'),
                'instructions' => __('statamic::fieldtypes.integer.config.max'),
                'type' => 'integer',
                'width' => '50',
            ],
        ];
    }

    public function toFieldArray(): array
    {
        return [
            'type' => 'integer',
            'min' => $this->config('min'),
            'max' => $this->config('max'),
            ...Arr::except($this->config(), ['type', 'min', 'max']),
        ];
    }

    public function example(): ?array
    {
        return [
            'config' => [
                'display' => 'How many cups of coffee do you drink per day?',
            ],
            'value' => 4,
        ];
    }
}
