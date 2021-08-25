<?php

namespace Statamic\GraphQL\Queries;

use GraphQL\Type\Definition\Type;
use Statamic\Facades;
use Statamic\Facades\GraphQL;
use Statamic\GraphQL\Types\EntryInterface;

class EntryQuery extends Query
{
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

        return $query->limit(1)->get()->first();
    }
}
