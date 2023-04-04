<?php

namespace Statamic\GraphQL\Queries;

use Facades\Statamic\API\ResourceAuthorizer;
use GraphQL\Type\Definition\Type;
use Statamic\Facades\GlobalSet;
use Statamic\Facades\GraphQL;
use Statamic\Facades\Site;
use Statamic\GraphQL\Types\GlobalSetInterface;

class GlobalSetsQuery extends Query
{
    protected $attributes = [
        'name' => 'globalSets',
    ];

    public function type(): Type
    {
        return GraphQL::listOf(GraphQL::type(GlobalSetInterface::NAME));
    }

    public function resolve($root, $args)
    {
        $allowed = ResourceAuthorizer::allowedSubResources('graphql', 'globals');

        $site = Site::default()->handle();

        return GlobalSet::all()->map->in($site)->filter(fn ($set) => in_array($set->handle(), $allowed));
    }
}
