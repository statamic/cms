<?php

namespace Statamic\GraphQL\Queries;

use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Statamic\Facades\Asset;
use Statamic\Facades\AssetContainer;
use Statamic\GraphQL\Types\AssetInterface;

class AssetQuery extends Query
{
    public function type(): Type
    {
        return GraphQL::type(AssetInterface::NAME);
    }

    public function args(): array
    {
        return [
            'id' => Type::string(),
            'container' => Type::string(),
            'path' => Type::string(),
        ];
    }

    public function resolve($root, $args)
    {
        if ($id = $args['id'] ?? null) {
            return Asset::find($id);
        }

        return AssetContainer::findByHandle($args['container'])->asset($args['path']);
    }
}
