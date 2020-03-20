<?php

namespace Statamic\Query\Scopes\Filters\Fields;

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
                ]
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
}
