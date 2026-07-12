<?php

namespace Statamic\GraphQL\Types;

use Statamic\Facades\GraphQL;

class LabeledValueType extends \Rebing\GraphQL\Support\Type
{
    const NAME = 'LabeledValue';

    protected $attributes = [
        'name' => self::NAME,
    ];

    public function fields(): array
    {
        return [
            'value' => [
                'type' => GraphQL::string(),
                'resolve' => function ($item) {
                    return $item->value();
                },
            ],
            'label' => [
                'type' => GraphQL::string(),
                'resolve' => function ($item) {
                    return $item->label();
                },
            ],
        ];
    }
}
