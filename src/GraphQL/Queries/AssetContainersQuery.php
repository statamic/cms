<?php

namespace Statamic\GraphQL\Queries;

use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Statamic\Facades\AssetContainer;
use Statamic\GraphQL\Types\AssetContainerType;

class AssetContainersQuery extends Query
{
    public function type(): Type
    {
        return Type::listOf(GraphQL::type(AssetContainerType::NAME));
    }

    public function resolve($root, $args)
    {
        return AssetContainer::all();
    }
}
