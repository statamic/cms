<?php

namespace Statamic\GraphQL\Queries;

use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Statamic\Facades\AssetContainer;
use Statamic\GraphQL\Types\AssetContainerType;

class AssetContainerQuery extends Query
{
    public function type(): Type
    {
        return GraphQL::type(AssetContainerType::NAME);
    }

    public function args(): array
    {
        return [
            'handle' => Type::string(),
        ];
    }

    public function resolve($root, $args)
    {
        return AssetContainer::findByHandle($args['handle']);
    }
}
