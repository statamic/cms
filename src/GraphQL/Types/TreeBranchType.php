<?php

namespace Statamic\GraphQL\Types;

use Statamic\Facades\GraphQL;

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
                'type' => GraphQL::nonNull(GraphQL::int()),
            ],
            'page' => [
                'type' => GraphQL::type(PageInterface::NAME),
            ],
            'children' => [
                'type' => GraphQL::listOf(GraphQL::type(self::NAME)),
            ],
        ];
    }
}
