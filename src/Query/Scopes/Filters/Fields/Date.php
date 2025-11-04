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
                'inline' => true,
                'full_width' => true,
                'if' => [
                    'operator' => 'contains_any >, <',
                ],
                'required' => false,
            ],
            'range_value' => [
                'type' => 'date',
                'inline' => true,
                'mode' => 'range',
                'full_width' => true,
                'if' => [
                    'operator' => 'between',
                ],
                'required' => false,
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
}
