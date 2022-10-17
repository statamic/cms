<?php

namespace Statamic\Query\Scopes\Filters\Fields;

use Statamic\Support\Arr;

class Number extends FieldtypeFilter
{
    public function fieldItems()
    {
        return [
            'operator' => [
                'type' => 'select',
                'placeholder' => __('Select Operator'),
                'options' => [
                    '=' => __('Is'),
                    '<>' => __('Isn\'t'),
                    '>' => __('Greater than'),
                    '>=' => __('Greater than or is'),
                    '<' => __('Less than'),
                    '<=' => __('Less than or is'),
                ],
                'default' => '=',
            ],
            'value' => [
                'type' => 'text',
                'placeholder' => __('Value'),
                'if' => [
                    'operator' => 'not empty',
                ],
            ],
        ];
    }

    public function apply($query, $handle, $values)
    {
        $operator = $values['operator'];
        $value = $values['value'];

        $query->where($handle, $operator, $value);
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
