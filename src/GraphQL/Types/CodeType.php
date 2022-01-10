<?php

namespace Statamic\GraphQL\Types;

use Statamic\Facades\GraphQL;

class CodeType extends \Rebing\GraphQL\Support\Type
{
    const NAME = 'Code';

    protected $attributes = [
        'name' => self::NAME,
    ];

    public function fields(): array
    {
        return [
            'code' => [
                'type' => GraphQL::nonNull(GraphQL::string()),
            ],
            'mode' => [
                'type' => GraphQL::nonNull(GraphQL::string()),
            ],
        ];
    }
}
