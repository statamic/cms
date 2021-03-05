<?php

namespace Statamic\GraphQL\Queries;

use GraphQL\Type\Definition\Type;
use Statamic\Facades\Asset;
use Statamic\Facades\GraphQL;
use Statamic\GraphQL\Middleware\ResolvePage;
use Statamic\GraphQL\Types\AssetInterface;
use Statamic\Support\Str;

class AssetsQuery extends Query
{
    protected $attributes = [
        'name' => 'assets',
    ];

    protected $middleware = [
        ResolvePage::class,
    ];

    public function type(): Type
    {
        return GraphQL::paginate(GraphQL::type(AssetInterface::NAME));
    }

    public function args(): array
    {
        return [
            'container' => GraphQL::nonNull(GraphQL::string()),
            'limit' => GraphQL::int(),
            'page' => GraphQL::int(),
            'sort' => GraphQL::listOf(GraphQL::string()),
        ];
    }

    public function resolve($root, $args)
    {
        $query = Asset::query()->where('container', $args['container']);

        if ($filters = $args['filter'] ?? null) {
            $this->filterQuery($query, $filters);
        }

        if ($sort = $args['sort'] ?? null) {
            $this->sortQuery($query, $sort);
        }

        return $query->paginate($args['limit'] ?? 1000);
    }

    private function sortQuery($query, $sorts)
    {
        foreach ($sorts as $sort) {
            $order = 'asc';

            if (Str::contains($sort, ' ')) {
                [$sort, $order] = explode(' ', $sort);
            }

            $query->orderBy($sort, $order);
        }
    }
}
