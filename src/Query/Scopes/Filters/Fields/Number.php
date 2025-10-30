<?php

namespace Statamic\Query\Scopes\Filters\Fields;

use Statamic\Support\Arr;

abstract class Number extends FieldtypeFilter
{
    abstract protected function valueFieldtype();

    public function fieldItems()
    {
        return [
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
                    'null' => __('Empty'),
                    'not-null' => __('Not empty'),
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
        $operator = $values['operator'];
        $value = $values['value'];

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
        $value = $values['value'];

        return $field.' '.strtolower($translatedOperator).' '.$value;
    }
}
