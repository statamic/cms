<?php

namespace Statamic\GraphQL\Queries;

use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Statamic\Facades\Collection;
use Statamic\GraphQL\Types\CollectionType;

class CollectionQuery extends Query
{
    public function type(): Type
    {
        return GraphQL::type(CollectionType::NAME);
    }

    public function args(): array
    {
        return [
            'handle' => Type::string(),
        ];
    }

    public function resolve($root, $args)
    {
        return Collection::findByHandle($args['handle']);
    }
}
