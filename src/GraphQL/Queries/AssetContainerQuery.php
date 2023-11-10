<?php

namespace Statamic\GraphQL\Queries;

use Facades\Statamic\API\ResourceAuthorizer;
use GraphQL\Type\Definition\Type;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\GraphQL;
use Statamic\GraphQL\Middleware\AuthorizeSubResources;
use Statamic\GraphQL\Types\AssetContainerType;

class AssetContainerQuery extends Query
{
    protected $attributes = [
        'name' => 'assetContainer',
    ];

    protected $middleware = [
        AuthorizeSubResources::class,
    ];

    public function type(): Type
    {
        return GraphQL::type(AssetContainerType::NAME);
    }

    public function args(): array
    {
        return [
            'handle' => GraphQL::nonNull(GraphQL::string()),
        ];
    }

    public function resolve($root, $args)
    {
        return AssetContainer::findByHandle($args['handle']);
    }

    public function subResourceArg()
    {
        return 'handle';
    }

    public function allowedSubResources()
    {
        return ResourceAuthorizer::allowedSubResources('graphql', 'assets');
    }
}
