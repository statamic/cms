<?php

namespace Statamic\GraphQL\Queries;

use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Statamic\Facades\Entry;
use Statamic\GraphQL\Middleware\ResolvePage;
use Statamic\GraphQL\Types\EntryInterface;
use Statamic\GraphQL\Types\JsonArgument;
use Statamic\Support\Arr;
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
        foreach ($filters as $field => $definitions) {
            if (! is_array($definitions)) {
                $definitions = [['equals' => $definitions]];
            }

            if (Arr::assoc($definitions)) {
                $definitions = collect($definitions)->map(function ($value, $key) {
                    return [$key => $value];
                })->values()->all();
            }

            foreach ($definitions as $definition) {
                $condition = array_keys($definition)[0];
                $value = array_values($definition)[0];
                $this->queryCondition($query, $field, $condition, $value);
            }
        }
    }
}
