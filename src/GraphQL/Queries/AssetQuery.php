<?php

namespace Statamic\GraphQL\Queries;

use Facades\Statamic\API\ResourceAuthorizer;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\ValidationException;
use Statamic\Facades\Asset;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\GraphQL;
use Statamic\GraphQL\Middleware\AuthorizeSubResources;
use Statamic\GraphQL\Types\AssetInterface;

class AssetQuery extends Query
{
    protected $attributes = [
        'name' => 'asset',
    ];

    protected $middleware = [
        AuthorizeSubResources::class,
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
            $asset = Asset::find($id);
        } else {
            $asset = AssetContainer::findByHandle($args['container'])->asset($args['path']);
        }

        // The middleware will take care of authorization when using `container` arg,
        // but this is still required when the user queries by the asset `id` arg.
        if ($asset && ! in_array($container = $asset->container()->handle(), $this->allowedSubResources())) {
            throw ValidationException::withMessages([
                'container' => 'Forbidden: '.$container,
            ]);
        }

        return $asset;
    }

    public function subResourceArg()
    {
        return 'container';
    }

    public function allowedSubResources()
    {
        return ResourceAuthorizer::allowedSubResources('graphql', 'assets');
    }
}
