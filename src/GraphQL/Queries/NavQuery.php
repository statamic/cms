<?php

namespace Statamic\GraphQL\Queries;

use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Statamic\Facades\Nav;
use Statamic\GraphQL\Types\NavType;

class NavQuery extends Query
{
    public function type(): Type
    {
        return GraphQL::type(NavType::NAME);
    }

    public function args(): array
    {
        return [
            'handle' => Type::string(),
        ];
    }

    public function resolve($root, $args)
    {
        return Nav::findByHandle($args['handle']);
    }
}
