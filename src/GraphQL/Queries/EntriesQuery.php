<?php

namespace Statamic\GraphQL\Queries;

use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Query;
use Statamic\Facades\Entry;
use Statamic\GraphQL\Types\EntryInterface;

class EntriesQuery extends Query
{
    public function __construct()
    {
        EntryInterface::addTypes();
    }

    public function type(): Type
    {
        return Type::listOf(GraphQL::type(EntryInterface::NAME));
    }

    public function args(): array
    {
        return [
            'collection' => Type::listOf(Type::string()),
        ];
    }

    public function resolve($root, $args)
    {
        $query = Entry::query();

        if ($collection = $args['collection'] ?? null) {
            $query->whereIn('collection', $collection);
        }

        return $query->get();
    }
}
