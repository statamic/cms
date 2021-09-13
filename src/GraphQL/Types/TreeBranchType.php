<?php

namespace Statamic\GraphQL\Types;

use Statamic\Facades\GraphQL;

abstract class TreeBranchType extends \Rebing\GraphQL\Support\Type
{
    public function __construct()
    {
        $this->attributes['name'] = static::NAME;
    }

    public function fields(): array
    {
        return [
            'depth' => [
                'type' => GraphQL::nonNull(GraphQL::int()),
            ],
            'children' => [
                'type' => GraphQL::listOf(GraphQL::type(static::NAME)),
            ],
        ];
    }
}
