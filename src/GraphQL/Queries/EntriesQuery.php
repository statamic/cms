<?php

namespace Statamic\GraphQL\Queries;

use GraphQL\Type\Definition\Type;
use Statamic\Facades\Entry;
use Statamic\Facades\GraphQL;
use Statamic\GraphQL\Middleware\ResolvePage;
use Statamic\GraphQL\Types\EntryInterface;
use Statamic\GraphQL\Types\JsonArgument;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use Statamic\Tags\Concerns\QueriesConditions;

use Statamic\Contracts\Taxonomies\Term;

class EntriesQuery extends Query
{
    use QueriesConditions;

    protected $attributes = [
        'name' => 'entries',
    ];

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

    private function filterQuery($query, $filters)
    {
        collect($filters)->filter(function ($value, $key) {
            return $key === 'taxonomy' || Str::startsWith($key, 'taxonomy:');
        })->each(function ($values, $param) use ($query) {
            $taxonomy = substr($param, 9);
            [$taxonomy, $modifier] = array_pad(explode(':', $taxonomy), 2, 'any');

            if (is_string($values)) {
                $values = array_filter(explode('|', $values));
            }

            if (is_null($values) || (is_iterable($values) && count($values) === 0)) {
                return;
            }

            $values = collect($values)->map(function ($term) use ($taxonomy) {
                if ($term instanceof Term) {
                    return $term->id();
                }

                return Str::contains($term, '::') ? $term : $taxonomy.'::'.$term;
            });

            if ($modifier === 'all') {
                $values->each(function ($value) use ($query) {
                    $query->whereTaxonomy($value);
                });
            } elseif ($modifier === 'any') {
                $query->whereTaxonomyIn($values->all());
            }
        });

        if (isset($filters['taxonomy'])) {
            unset($filters['taxonomy']);
        }

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
}
