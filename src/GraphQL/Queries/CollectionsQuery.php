<?php

namespace Statamic\GraphQL\Queries;

use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Statamic\Facades\Collection;
use Statamic\GraphQL\Types\CollectionType;

class CollectionsQuery extends Query
{
    public function type(): Type
    {
        return Type::listOf(GraphQL::type(CollectionType::NAME));
    }

    public function resolve($root, $args)
    {
        return Collection::all();
    }
}
