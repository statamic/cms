<?php

namespace Statamic\GraphQL\Queries;

use GraphQL\Type\Definition\Type;
use Statamic\Facades\GraphQL;
use Statamic\Facades\Nav;
use Statamic\GraphQL\Types\NavType;

class NavsQuery extends Query
{
    protected $attributes = [
        'name' => 'navs',
    ];

    public function type(): Type
    {
        return GraphQL::listOf(GraphQL::type(NavType::NAME));
    }

    public function resolve($root, $args)
    {
        return Nav::all();
    }
}
