<?php

namespace Statamic\Query\Scopes\Filters\Fields;

use Illuminate\Support\Carbon;
use Statamic\Support\Arr;

class Date extends FieldtypeFilter
{
    public function fieldItems()
    {
        return [
            'operator' => [
                'type' => 'select',
                'placeholder' => __('Select Operator'),
                'options' => [
                    '<' => __('Before'),
                    '>' => __('After'),
                    'between' => __('Between'),
                    'null' => __('Empty'),
                    'not-null' => __('Not empty'),
                ],
            ],
            'value' => [
                'type' => 'date',
                'full_width' => true,
                'clearable' => false,
                'if' => [
                    'operator' => 'contains_any >, <',
                ],
            ],
            'range_value' => [
                'type' => 'date',
                'mode' => 'range',
                'full_width' => true,
                'clearable' => false,
                'if' => [
                    'operator' => 'between',
                ],
            ],
        ];
    }

    public function apply($query, $handle, $values)
    {
        $operator = $values['operator'];

        if ($operator == 'between') {
            $query->whereDate($handle, '>=', Carbon::parse($values['range_value']['start']));
            $query->whereDate($handle, '<=', Carbon::parse($values['range_value']['end']));

            return;
        }

        $value = Carbon::parse($values['value']);

        match ($operator) {
            'null' => $query->whereNull($handle),
            'not-null' => $query->whereNotNull($handle),
            default => $query->where($handle, $operator, $value),
        };
    }

    public function badge($values)
    {
        $field = $this->fieldtype->field()->display();
        $operator = $values['operator'];
        $translatedOperator = Arr::get($this->fieldItems(), "operator.options.{$operator}");

        if ($operator == 'between') {
            return $field.' '.strtolower($translatedOperator).' '.$values['range_value']['start'].' '.__('and').' '.$values['range_value']['end'];
        }

        return $field.' '.strtolower($translatedOperator).' '.$values['value'];
    }

    public function isComplete($values): bool
    {
        $values = array_filter($values);

        if (! $operator = Arr::get($values, 'operator')) {
            return false;
        }

        if ($operator === 'between') {
            return Arr::has($values, 'range_value.start') && Arr::has($values, 'range_value.end');
        }

        return Arr::has($values, 'value');
    }
}
