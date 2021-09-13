<?php

namespace Statamic\Fieldtypes;

use Statamic\Facades\GraphQL;
use Statamic\Fields\Fieldtype;
use Statamic\Fields\LabeledValue;
use Statamic\GraphQL\Types\LabeledValueType;

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
            'default' => [
                'display' => __('Default Value'),
                'instructions' => __('statamic::messages.fields_default_instructions'),
                'type' => 'text',
                'width' => 50,
            ],
        ];
    }

    public function augment($value)
    {
        $label = is_null($value) ? null : array_get($this->config('options'), $value, $value);

        return new LabeledValue($value, $label);
    }

    public function toGqlType()
    {
        return [
            'type' => GraphQL::type(LabeledValueType::NAME),
            'resolve' => function ($item, $args, $context, $info) {
                $resolved = $item->resolveGqlValue($info->fieldName);

                return $resolved->value() ? $resolved : null;
            },
        ];
    }
}
