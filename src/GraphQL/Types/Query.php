<?php

namespace Statamic\GraphQL\Types;

use GraphQL\Type\Definition\Type;

class Query extends ObjectType
{
    public static function name(): string
    {
        return 'Query';
    }

    public function config(array $args): array
    {
        return [
            'name' => 'Query',
            'fields' => [
                'ping' => [
                    'type' => Type::string(),
                    'resolve' => function () {
                        return 'pong';
                    },
                ],
            ],
        ];
    }
}
