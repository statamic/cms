<?php

namespace Statamic\GraphQL\Types;

use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;

class TreeBranchType extends \Rebing\GraphQL\Support\Type
{
    const NAME = 'TreeBranch';

    protected $attributes = [
        'name' => self::NAME,
    ];

    public function fields(): array
    {
        return [
            'depth' => [
                'type' => Type::nonNull(Type::int()),
            ],
            'page' => [
                'type' => GraphQL::type(PageInterface::NAME),
            ],
            'children' => [
                'type' => Type::listOf(GraphQL::type(self::NAME)),
            ],
        ];
    }
}
