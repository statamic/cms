<?php

namespace Statamic\GraphQL\Queries;

use GraphQL\Type\Definition\Type;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\GraphQL;
use Statamic\GraphQL\Types\AssetContainerType;

class AssetContainersQuery extends Query
{
    protected $attributes = [
        'name' => 'assetContainers',
    ];

    public function type(): Type
    {
        return GraphQL::listOf(GraphQL::type(AssetContainerType::NAME));
    }

    public function resolve($root, $args)
    {
        return AssetContainer::all();
    }
}
