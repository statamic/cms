<?php

namespace Statamic\Fieldtypes;

use Statamic\Facades\GraphQL;
use Statamic\Fields\Fieldtype;
use Statamic\Fields\LabeledValue;
use Statamic\GraphQL\Types\LabeledValueType;

class Checkboxes extends Fieldtype
{
    protected $selectableInForms = true;

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
            'default' => [
                'display' => __('Default Value'),
                'instructions' => __('statamic::messages.fields_default_instructions'),
                'type' => 'text',
                'width' => 50,
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

    public function toGqlType()
    {
        return [
            'type' => GraphQL::listOf(GraphQL::type(LabeledValueType::NAME)),
            'resolve' => function ($item, $args, $context, $info) {
                $resolved = $item->resolveGqlValue($info->fieldName);

                if (empty($resolved)) {
                    return null;
                }

                return collect($resolved)->map(function ($item) {
                    return new LabeledValue($item['value'], $item['label']);
                })->all();
            },
        ];
    }
}
