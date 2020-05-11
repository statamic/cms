<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Checkboxes extends Fieldtype
{
    protected function configFieldItems(): array
    {
        return [
            'inline' => [
                'display' => __('Inline'),
                'instructions' => __('statamic::fieldtypes.checkboxes.config.inline'),
                'type' => 'toggle',
                'width' => 50,
            ],
            'options' => [
                'display' => __('Options'),
                'instructions' => __('statamic::fieldtypes.checkboxes.config.options'),
                'type' => 'array',
                'key_header' => __('Key (Value)'),
                'value_header' => __('Label'),
            ],
        ];
    }

    public function augment($values)
    {
        if (is_null($values)) {
            return [];
        }

        return collect($values)->map(function ($value) {
            return [
                'key' => $value,
                'value' => $value,
                'label' => array_get($this->config('options'), $value, $value),
            ];
        })->all();
    }
}
