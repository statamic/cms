<?php

namespace Statamic\GraphQL\Queries;

use GraphQL\Type\Definition\Type;
use Statamic\Facades\Entry;
use Statamic\Facades\GraphQL;
use Statamic\GraphQL\Middleware\AllowedFilters;
use Statamic\GraphQL\Middleware\ResolvePage;
use Statamic\GraphQL\Queries\Concerns\FiltersQuery;
use Statamic\GraphQL\Types\EntryInterface;
use Statamic\GraphQL\Types\JsonArgument;
use Statamic\Support\Str;

class EntriesQuery extends Query
{
    use FiltersQuery;

    protected $attributes = [
        'name' => 'entries',
    ];

    protected $middleware = [
        ResolvePage::class,
        AllowedFilters::class,
    ];

    public function type(): Type
    {
        return GraphQL::paginate(GraphQL::type(EntryInterface::NAME));
    }

    public function args(): array
    {
        return [
            'collection' => GraphQL::listOf(GraphQL::string()),
            'limit' => GraphQL::int(),
            'page' => GraphQL::int(),
            'filter' => GraphQL::type(JsonArgument::NAME),
            'sort' => GraphQL::listOf(GraphQL::string()),
        ];
    }

    public function resolve($root, $args)
    {
        $query = Entry::query();

        if ($collection = $args['collection'] ?? null) {
            $query->whereIn('collection', $collection);
        }

        $this->filterQuery($query, $args['filter'] ?? []);

        $this->sortQuery($query, $args['sort'] ?? []);

        return $query->paginate($args['limit'] ?? 1000);
    }

    private function sortQuery($query, $sorts)
    {
        if (empty($sorts)) {
            $sorts = ['id'];
        }

        foreach ($sorts as $sort) {
            $order = 'asc';

            if (Str::contains($sort, ' ')) {
                [$sort, $order] = explode(' ', $sort);
            }

            $query->orderBy($sort, $order);
        }
    }

    public function allowedFilters($args)
    {
        $config = config('statamic.graphql.resources.collections');

        if ($config === true) {
            return collect();
        }

        $collections = collect($args['collection'] ?? []);

        // Get the "allowed_filters" from all the collections, filtering out any that don't appear in all of them.
        // And a collection named "*" will apply to all collections.
        return $collections
            ->map(fn ($collection) => $config[$collection]['allowed_filters'] ?? [])
            ->reduce(function ($carry, $allowedFilters) use ($config) {
                return $carry->intersect($allowedFilters)->merge($config['*']['allowed_filters'] ?? []);
            }, collect($config[$collections[0]]['allowed_filters'] ?? []));
    }
}
