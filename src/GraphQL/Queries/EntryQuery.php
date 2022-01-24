<?php

namespace Statamic\GraphQL\Queries;

use GraphQL\Type\Definition\Type;
use Statamic\Facades;
use Statamic\Facades\GraphQL;
use Statamic\GraphQL\Types\EntryInterface;
use Statamic\GraphQL\Types\JsonArgument;
use Statamic\Support\Arr;
use Statamic\Tags\Concerns\QueriesConditions;

class EntryQuery extends Query
{
    use QueriesConditions;

    protected $attributes = [
        'name' => 'entry',
    ];

    public function type(): Type
    {
        return GraphQL::type(EntryInterface::NAME);
    }

    public function args(): array
    {
        return [
            'id' => GraphQL::string(),
            'slug' => GraphQL::string(),
            'collection' => GraphQL::string(),
            'uri' => GraphQL::string(),
            'site' => GraphQL::string(),
            'filter' => GraphQL::type(JsonArgument::NAME),
        ];
    }

    public function resolve($root, $args)
    {
        $query = Facades\Entry::query();

        if ($id = $args['id'] ?? null) {
            $query->where('id', $id);
        }

        if ($slug = $args['slug'] ?? null) {
            $query->where('slug', $slug);
        }

        if ($collection = $args['collection'] ?? null) {
            $query->where('collection', $collection);
        }

        if ($uri = $args['uri'] ?? null) {
            $query->where('uri', $uri);
        }

        if ($site = $args['site'] ?? null) {
            $query->where('site', $site);
        }

        $this->filterQuery($query, $args['filter'] ?? []);

        return $query->limit(1)->get()->first();
    }

    private function filterQuery($query, $filters)
    {
        if (! isset($filters['status']) && ! isset($filters['published'])) {
            $filters['status'] = 'published';
        }

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
