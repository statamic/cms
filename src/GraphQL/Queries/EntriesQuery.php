<?php

namespace Statamic\GraphQL\Queries;

use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Statamic\Facades\Entry;
use Statamic\GraphQL\Middleware\ResolvePage;
use Statamic\GraphQL\Types\EntryInterface;
use Statamic\GraphQL\Types\JsonArgument;
use Statamic\Tags\Concerns\QueriesConditions;

class EntriesQuery extends Query
{
    use QueriesConditions;

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
            'filter' => GraphQL::type(JsonArgument::NAME),
        ];
    }

    public function resolve($root, $args)
    {
        $query = Entry::query();

        if ($collection = $args['collection'] ?? null) {
            $query->whereIn('collection', $collection);
        }

        if ($filters = $args['filter'] ?? null) {
            $this->filterQuery($query, $filters);
        }

        return $query->paginate($args['limit'] ?? 1000);
    }

    private function filterQuery($query, $filters)
    {
        foreach ($filters as $field => $conditions) {
            foreach ($conditions as $condition => $value) {
                $this->queryCondition($query, $field, $condition, $value);
            }
        }
    }
}
