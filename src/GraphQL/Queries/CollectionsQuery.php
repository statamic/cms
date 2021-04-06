<?php

namespace Statamic\GraphQL\Queries;

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
        return Collection::all();
    }
}
