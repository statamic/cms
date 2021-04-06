<?php

namespace Statamic\GraphQL\Queries;

use GraphQL\Type\Definition\Type;
use Statamic\Facades\GraphQL;
use Statamic\Facades\Site;
use Statamic\GraphQL\Types\SiteType;

class SitesQuery extends Query
{
    protected $attributes = [
        'name' => 'sites',
    ];

    public function type(): Type
    {
        return GraphQL::listOf(GraphQL::type(SiteType::NAME));
    }

    public function resolve($root, $args)
    {
        return Site::all();
    }
}
