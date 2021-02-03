<?php

namespace Statamic\GraphQL\Queries;

use GraphQL\Type\Definition\Type;
use Statamic\Facades\Collection;
use Statamic\Facades\GraphQL;
use Statamic\GraphQL\Types\CollectionType;

class CollectionQuery extends Query
{
    protected $attributes = [
        'name' => 'collection',
    ];

    public function type(): Type
    {
        return GraphQL::type(CollectionType::NAME);
    }

    public function args(): array
    {
        return [
            'handle' => GraphQL::string(),
        ];
    }

    public function resolve($root, $args)
    {
        return Collection::findByHandle($args['handle']);
    }
}
