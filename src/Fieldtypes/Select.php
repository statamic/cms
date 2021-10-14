<?php

namespace Statamic\Fieldtypes;

use Statamic\Facades\GraphQL;
use Statamic\Fields\Fieldtype;
use Statamic\Fields\LabeledValue;
use Statamic\GraphQL\Types\LabeledValueType;
use Statamic\Support\Arr;

class Select extends Fieldtype
{
    protected $selectableInForms = true;

    protected function configFieldItems(): array
    {
        return [
            'placeholder' => [
                'display' => __('Placeholder'),
                'instructions' => __('statamic::fieldtypes.select.config.placeholder'),
                'type' => 'text',
                'default' => '',
                'width' => 50,
            ],
            'options' => [
                'display' => __('Options'),
                'instructions' => __('statamic::fieldtypes.select.config.options'),
                'type' => 'array',
                'key_header' => __('Key'),
                'value_header' => __('Label').' ('.__('Optional').')',
                'add_button' => __('Add Option'),
            ],
            'multiple' => [
                'display' => __('Multiple'),
                'instructions' => __('statamic::fieldtypes.select.config.multiple'),
                'type' => 'toggle',
                'default' => false,
                'width' => 50,
            ],
            'max_items' => [
                'display' => __('Max Items'),
                'instructions' => __('statamic::messages.max_items_instructions'),
                'type' => 'integer',
                'width' => 50,
            ],
            'clearable' => [
                'display' => __('Clearable'),
                'instructions' => __('statamic::fieldtypes.select.config.clearable'),
                'type' => 'toggle',
                'default' => false,
                'width' => 50,
            ],
            'searchable' => [
                'display' => __('Searchable'),
                'instructions' => __('statamic::fieldtypes.select.config.searchable'),
                'type' => 'toggle',
                'default' => true,
                'width' => 50,
            ],
            'taggable' => [
                'display' => __('Taggable'),
                'instructions' => __('statamic::fieldtypes.select.config.taggable'),
                'type' => 'toggle',
                'default' => false,
                'display' => __('Allow additions'),
                'width' => 50,
            ],
            'push_tags' => [
                'display' => __('Push Tags'),
                'instructions' => __('statamic::fieldtypes.select.config.push_tags'),
                'type' => 'toggle',
                'default' => false,
                'width' => 50,
            ],
            'cast_booleans' => [
                'display' => __('Cast Booleans'),
                'instructions' => __('statamic::fieldtypes.any.config.cast_booleans'),
                'type' => 'toggle',
                'default' => false,
                'width' => 50,
            ],
            'default' => [
                'display' => __('Default Value'),
                'instructions' => __('statamic::messages.fields_default_instructions'),
                'type' => 'text',
                'width' => 50,
            ],
        ];
    }

    protected $indexComponent = 'tags';

    public function preProcessIndex($value)
    {
        if (! $value) {
            return [];
        }

        return collect(Arr::wrap($value))->map(function ($value) {
            return array_get($this->field->get('options'), $value, $value);
        })->all();
    }

    public function augment($value)
    {
        if ($this->config('multiple')) {
            return collect($value)->map(function ($value) {
                return [
                    'key' => $value,
                    'value' => $value,
                    'label' => $this->getLabel($value),
                ];
            })->all();
        }

        throw_if(is_array($value), new MultipleValuesEncounteredException($this));

        return new LabeledValue($value, $this->getLabel($value));
    }

    public function preProcess($value)
    {
        // Cannot use Arr::wrap() here because it will convert null to an empty array.
        $value = is_array($value) ? $value : [$value];

        $values = collect($value)->map(function ($value) {
            return $this->config('cast_booleans') ? $this->castFromBoolean($value) : $value;
        });

        return $this->config('multiple') ? $values->all() : $values->first();
    }

    public function preProcessConfig($value)
    {
        return $value;
    }

    public function process($value)
    {
        $values = collect(Arr::wrap($value))->map(function ($value) {
            return $this->config('cast_booleans') ? $this->castToBoolean($value) : $value;
        });

        return $this->config('multiple') ? $values->all() : $values->first();
    }

    public function toGqlType()
    {
        return $this->config('multiple')
            ? $this->multiSelectGqlType()
            : $this->singleSelectGqlType();
    }

    private function singleSelectGqlType()
    {
        return [
            'type' => GraphQL::type(LabeledValueType::NAME),
            'resolve' => function ($item, $args, $context, $info) {
                $resolved = $item->resolveGqlValue($info->fieldName);

                return is_null($resolved->value()) ? null : $resolved;
            },
        ];
    }

    private function multiSelectGqlType()
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

    private function getLabel($actualValue)
    {
        $value = $actualValue;

        if ($this->config('cast_booleans')) {
            $value = $this->castFromBoolean($value);
        }

        return $this->isOption($value)
            ? Arr::get($this->config('options'), $value, $value)
            : $actualValue;
    }

    private function castToBoolean($value)
    {
        if ($value === 'true') {
            return true;
        } elseif ($value === 'false') {
            return false;
        } elseif ($value === 'null') {
            return null;
        }

        return $value;
    }

    private function castFromBoolean($value)
    {
        if ($value === true) {
            return 'true';
        } elseif ($value === false) {
            return 'false';
        } elseif ($value === null) {
            return 'null';
        }

        return $value;
    }

    private function isOption($value)
    {
        return Arr::isAssoc($options = $this->config('options'))
            ? in_array($value, array_keys($options), true)
            : in_array($value, $options, true);
    }
}
