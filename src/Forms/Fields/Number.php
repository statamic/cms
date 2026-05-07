<?php

namespace Statamic\Forms\Fields;

use Statamic\Support\Arr;

class Number extends FormFieldtype
{
    protected static $fieldtype = 'integer';

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
}
