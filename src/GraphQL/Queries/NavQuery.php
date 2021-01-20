<?php

namespace Statamic\GraphQL\Queries;

use GraphQL\Type\Definition\Type;
use Statamic\Facades\GraphQL;
use Statamic\Facades\Nav;
use Statamic\GraphQL\Types\NavType;

class NavQuery extends Query
{
    protected $attributes = [
        'name' => 'nav',
    ];

    public function type(): Type
    {
        return GraphQL::type(NavType::NAME);
    }

    public function args(): array
    {
        return [
            'handle' => GraphQL::string(),
        ];
    }

    public function resolve($root, $args)
    {
        return Nav::findByHandle($args['handle']);
    }
}
