<?php

namespace Statamic\GraphQL\Queries;

use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Statamic\Facades\Asset;
use Statamic\GraphQL\Middleware\ResolvePage;
use Statamic\GraphQL\Types\AssetInterface;
use Statamic\Support\Str;

class AssetsQuery extends Query
{
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
            'container' => Type::nonNull(Type::string()),
            'limit' => Type::int(),
            'page' => Type::int(),
            'sort' => Type::listOf(Type::string()),
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
