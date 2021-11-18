<?php

namespace Statamic\Fieldtypes;

use Statamic\Facades\GraphQL;
use Statamic\Fields\LabeledValue;
use Statamic\GraphQL\Types\LabeledValueType;
use Statamic\Support\Arr;

trait HasSelectOptions
{
    protected function multiple()
    {
        return $this->config('multiple');
    }

    public function preProcessIndex($value)
    {
        $values = $this->preProcess($value);

        $values = collect(is_array($values) ? $values : [$values]);

        return $values->map(function ($value) {
            return $this->getLabel($value);
        })->all();
    }

    public function preProcess($value)
    {
        $multiple = $this->multiple();

        if ($value === null && $multiple) {
            return [];
        }

        $value = is_array($value) ? $value : [$value];

        $values = collect($value)->map(function ($value) {
            return $this->config('cast_booleans') ? $this->castFromBoolean($value) : $value;
        });

        return $multiple ? $values->all() : $values->first();
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

        return $this->multiple() ? $values->all() : $values->first();
    }

    public function augment($value)
    {
        if ($this->multiple() && is_null($value)) {
            return [];
        }

        if ($this->multiple()) {
            return collect($value)->map(function ($value) {
                return [
                    'key' => $value = $this->normalizeAugmentableValue($value),
                    'value' => $value,
                    'label' => $this->getLabel($value),
                ];
            })->all();
        }

        throw_if(is_array($value), new MultipleValuesEncounteredException($this));

        $value = $this->normalizeAugmentableValue($value);

        return new LabeledValue($value, $this->getLabel($value));
    }

    private function normalizeAugmentableValue($value)
    {
        if (! is_numeric($value)) {
            return $value;
        }

        if ($value == (int) $value) {
            return (int) $value;
        }

        if ($value == (float) $value) {
            return (string) $value;
        }

        return $value;
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

    private function isOption($value)
    {
        return Arr::isAssoc($options = $this->config('options') ?? [])
            ? in_array($value, array_keys($options), true)
            : in_array($value, $options, true);
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

    public function toGqlType()
    {
        return $this->multiple()
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
}
