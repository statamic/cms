<?php

namespace Statamic\GraphQL\Queries;

use Facades\Statamic\API\ResourceAuthorizer;
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
        $allowed = ResourceAuthorizer::allowedSubResources('graphql', 'assets');

        return AssetContainer::all()->filter(fn ($container) => in_array($container->handle(), $allowed));
    }
}
