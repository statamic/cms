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
}
