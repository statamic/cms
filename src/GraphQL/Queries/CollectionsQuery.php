<?php

namespace Statamic\GraphQL\Queries;

use Facades\Statamic\API\ResourceAuthorizer;
use GraphQL\Type\Definition\Type;
use Statamic\Facades\Collection;
use Statamic\Facades\GraphQL;
use Statamic\GraphQL\Types\CollectionType;

class CollectionsQuery extends Query
{
    protected $attributes = [
        'name' => 'collections',
    ];

    public function type(): Type
    {
        return GraphQL::listOf(GraphQL::type(CollectionType::NAME));
    }

    public function resolve($root, $args)
    {
        return Collection::all()->filter(function ($collection) {
            return in_array($collection->handle(), ResourceAuthorizer::allowedSubResources('graphql', 'collections'));
        });
    }
}
