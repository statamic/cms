<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;
use Statamic\Fields\LabeledValue;

class ButtonGroup extends Fieldtype
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
        ];
    }

    public function augment($value)
    {
        $label = is_null($value) ? null : array_get($this->config('options'), $value, $value);

        return new LabeledValue($value, $label);
    }
}
