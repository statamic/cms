<?php

namespace Statamic\Query\Scopes\Filters\Fields;

use Illuminate\Support\Arr;

/**
 * Handle width, height, shortest side, longest side queries.
 */
class Dimensions extends Integer
{
    public function fieldItems()
    {
        return [
            'dimension' => [
                'type' => 'select',
                'placeholder' => __('Dimension'),
                'options' => [
                    'width' => __('Width'),
                    'height' => __('Height'),
                    'longest' => __('Longest side'),
                    'shortest' => __('Shortest side'),
                ],
                'default' => 'width',
            ],
            'operator' => [
                'type' => 'select',
                'placeholder' => __('Select Operator'),
                'options' => [
                    '=' => __('Equals'),
                    '<>' => __('Not equals'),
                    '>' => __('Greater than'),
                    '>=' => __('Greater than or equals'),
                    '<' => __('Less than'),
                    '<=' => __('Less than or equals'),
                ],
                'default' => '=',
            ],
            'value' => [
                'type' => $this->valueFieldtype(),
                'placeholder' => __('Value'),
                'if' => [
                    'operator' => 'contains_any <>, >, >=, <, <=, =',
                ],
            ],
        ];
    }

    public function apply($query, $handle, $values)
    {
        $dimension = $values['dimension'];
        $operator = $values['operator'];
        $value = $values['value'];

        match ($dimension) {
            'width' => $query->where('width', $operator, $value),
            'height' => $query->where('height', $operator, $value),
            'longest' => $query->where(fn ($q) => $q
                ->where(fn ($qq) => $qq->whereIn('orientation', ['landscape', 'square'])->where('width', $operator, $value))
                ->orWhere(fn ($qq) => $qq->where('orientation', 'portrait')->where('height', $operator, $value))),
            'shortest' => $query->where(fn ($q) => $q
                ->where(fn ($qq) => $qq->whereIn('orientation', ['landscape', 'square'])->where('height', $operator, $value))
                ->orWhere(fn ($qq) => $qq->where('orientation', 'portrait')->where('width', $operator, $value))),
        };
    }

    public function badge($values)
    {
        $dimension = $values['dimension'];
        $translatedDimension = Arr::get($this->fieldItems(), "dimension.options.{$dimension}");
        $operator = $values['operator'];
        $translatedOperator = Arr::get($this->fieldItems(), "operator.options.{$operator}");

        return $translatedDimension.' '.strtolower($translatedOperator).' '.$values['value'];
    }

    public function isComplete($values): bool
    {
        $values = array_filter($values);

        return Arr::hasAll($values, ['dimension', 'operator', 'value']);
    }
}
