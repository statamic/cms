<?php

namespace Statamic\GraphQL\Types;

use GraphQL\Type\Definition\Type;

class Query extends ObjectType
{
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
