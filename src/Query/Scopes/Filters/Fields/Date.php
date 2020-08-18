<?php

namespace Statamic\Query\Scopes\Filters\Fields;

use Illuminate\Support\Carbon;

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
                ],
            ],
            'value' => [
                'type' => 'date',
                'required' => 'true',
                'if' => [
                    'operator' => 'not empty',
                ],
            ],
        ];
    }

    public function apply($query, $handle, $values)
    {
        $operator = $values['operator'];
        $value = Carbon::parse($values['value']);

        $query->where($handle, $operator, $value);
    }
}
