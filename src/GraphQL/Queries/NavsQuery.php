<?php

namespace Statamic\GraphQL\Queries;

use Facades\Statamic\API\ResourceAuthorizer;
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
        $allowed = ResourceAuthorizer::allowedSubResources('graphql', 'navs');

        return Nav::all()->filter(fn ($nav) => in_array($nav->handle(), $allowed));
    }
}
