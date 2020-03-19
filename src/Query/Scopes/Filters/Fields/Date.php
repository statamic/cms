<?php

namespace Statamic\Query\Scopes\Filters\Fields;

class Date extends FieldtypeFilter
{
    public function fieldItems()
    {
        return [
            'operator' => [
                'type' => 'select',
                'options' => [
                    '<' => __('Before'),
                    '>' => __('After'),
                ]
            ],
            'value' => [
                'type' => 'date',
                'required' => 'true',
            ],
        ];
    }

    public function apply($query, $handle, $values)
    {
        $query->where($handle, $values['operator'], $values['value']);
    }
}
