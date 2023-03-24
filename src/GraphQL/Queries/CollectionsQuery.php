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
        $allowed = ResourceAuthorizer::allowedSubResources('graphql', 'collections');

        return Collection::all()->filter(fn ($collection) => in_array($collection->handle(), $allowed));
    }
}
