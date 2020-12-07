<?php

namespace Statamic\GraphQL\Queries;

use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Statamic\Facades\Entry;
use Statamic\GraphQL\Middleware\ResolvePage;
use Statamic\GraphQL\Types\EntryInterface;

class EntriesQuery extends Query
{
    protected $middleware = [
        ResolvePage::class,
    ];

    public function type(): Type
    {
        return GraphQL::paginate(GraphQL::type(EntryInterface::NAME));
    }

    public function args(): array
    {
        return [
            'collection' => Type::listOf(Type::string()),
            'limit' => Type::int(),
            'page' => Type::int(),
        ];
    }

    public function resolve($root, $args)
    {
        $query = Entry::query();

        if ($collection = $args['collection'] ?? null) {
            $query->whereIn('collection', $collection);
        }

        return $query->paginate($args['limit'] ?? 1000);
    }
}
