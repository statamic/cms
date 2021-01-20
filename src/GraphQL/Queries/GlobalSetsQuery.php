<?php

namespace Statamic\GraphQL\Queries;

use GraphQL\Type\Definition\Type;
use Statamic\Facades\GlobalSet;
use Statamic\Facades\GraphQL;
use Statamic\Facades\Site;
use Statamic\GraphQL\Types\GlobalSetInterface;

class GlobalSetsQuery extends Query
{
    public function type(): Type
    {
        return GraphQL::listOf(GraphQL::type(GlobalSetInterface::NAME));
    }

    public function resolve($root, $args)
    {
        $site = Site::default()->handle();

        return GlobalSet::all()->map->in($site);
    }
}
