<?php

namespace Statamic\GraphQL\Queries;

use GraphQL\Type\Definition\Type;
use Statamic\Facades;
use Statamic\Facades\GraphQL;
use Statamic\GraphQL\Queries\Concerns\FiltersQuery;
use Statamic\GraphQL\Types\EntryInterface;
use Statamic\GraphQL\Types\JsonArgument;

class EntryQuery extends Query
{
    use FiltersQuery;

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
}
