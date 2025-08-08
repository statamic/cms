<?php

namespace Statamic\Query\Scopes\Filters\Fields;

use Statamic\Support\Arr;
use Statamic\Support\Str;

class Bard extends FieldtypeFilter
{
    public function fieldItems()
    {
        return [
            'operator' => [
                'type' => 'select',
                'placeholder' => __('Select Operator'),
                'options' => [
                    'like' => __('Contains'),
                    'null' => __('Empty'),
                    'not-null' => __('Not empty'),
                ],
                'default' => 'like',
            ],
            'value' => [
                'type' => 'text',
                'if' => [
                    'operator' => 'like',
                ],
                'required' => false,
            ],
        ];
    }

    public function apply($query, $handle, $values)
    {
        $operator = $values['operator'];
        $value = $values['value'];

        if ($operator === 'like') {
            $value = Str::ensureLeft($value, '%');
            $value = Str::ensureRight($value, '%');
        }

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
