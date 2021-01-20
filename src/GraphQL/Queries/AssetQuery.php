<?php

namespace Statamic\GraphQL\Queries;

use GraphQL\Type\Definition\Type;
use Statamic\Facades\Asset;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\GraphQL;
use Statamic\GraphQL\Types\AssetInterface;

class AssetQuery extends Query
{
    protected $attributes = [
        'name' => 'asset',
    ];

    public function type(): Type
    {
        return GraphQL::type(AssetInterface::NAME);
    }

    public function args(): array
    {
        return [
            'id' => GraphQL::string(),
            'container' => GraphQL::string(),
            'path' => GraphQL::string(),
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
