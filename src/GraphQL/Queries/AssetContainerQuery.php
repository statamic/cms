<?php

namespace Statamic\GraphQL\Queries;

use GraphQL\Type\Definition\Type;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\GraphQL;
use Statamic\GraphQL\Types\AssetContainerType;

class AssetContainerQuery extends Query
{
    protected $attributes = [
        'name' => 'assetContainer',
    ];

    public function type(): Type
    {
        return GraphQL::type(AssetContainerType::NAME);
    }

    public function args(): array
    {
        return [
            'handle' => GraphQL::string(),
        ];
    }

    public function resolve($root, $args)
    {
        return AssetContainer::findByHandle($args['handle']);
    }
}
