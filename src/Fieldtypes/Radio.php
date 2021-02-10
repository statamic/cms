<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;
use Statamic\Fields\LabeledValue;

class Radio extends Fieldtype
{
    protected function configFieldItems(): array
    {
        return [
            'options' => [
                'display' => __('Options'),
                'instructions' => __('statamic::fieldtypes.radio.config.options'),
                'type' => 'array',
                'value_header' => __('Label'),
            ],
            'inline' => [
                'display' => __('Inline'),
                'instructions' => __('statamic::fieldtypes.radio.config.inline'),
                'type' => 'toggle',
                'width' => 50,
            ],
            'cast_booleans' => [
                'display' => __('Cast Booleans'),
                'instructions' => __('statamic::fieldtypes.any.config.cast_booleans'),
                'type' => 'toggle',
                'default' => false,
                'width' => 50,
            ],
        ];
    }

    public function augment($value)
    {
        $label = is_null($value) ? null : array_get($this->config('options'), $value, $value);

        return new LabeledValue($value, $label);
    }

    public function preProcess($value)
    {
        if ($this->config('cast_booleans')) {
            if ($value === true) {
                return 'true';
            } elseif ($value === false) {
                return 'false';
            }
        }

        return $value;
    }

    public function process($value)
    {
        if ($this->config('cast_booleans')) {
            if ($value === 'true') {
                return true;
            } elseif ($value === 'false') {
                return false;
            }
        }

        return $value;
    }

    public function preProcessIndex($value)
    {
        return collect($this->config('options'))->get($value, $value);
    }
}
