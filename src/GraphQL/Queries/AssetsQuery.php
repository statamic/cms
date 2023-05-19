<?php

namespace Statamic\GraphQL\Queries;

use Facades\Statamic\API\FilterAuthorizer;
use Facades\Statamic\API\ResourceAuthorizer;
use GraphQL\Type\Definition\Type;
use Statamic\Facades\Asset;
use Statamic\Facades\GraphQL;
use Statamic\GraphQL\Middleware\AuthorizeFilters;
use Statamic\GraphQL\Middleware\AuthorizeSubResources;
use Statamic\GraphQL\Middleware\ResolvePage;
use Statamic\GraphQL\Queries\Concerns\FiltersQuery;
use Statamic\GraphQL\Types\AssetInterface;
use Statamic\GraphQL\Types\JsonArgument;
use Statamic\Support\Str;

class AssetsQuery extends Query
{
    use FiltersQuery;

    protected $attributes = [
        'name' => 'assets',
    ];

    protected $middleware = [
        AuthorizeSubResources::class,
        ResolvePage::class,
        AuthorizeFilters::class,
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
            'filter' => GraphQL::type(JsonArgument::NAME),
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

    public function subResourceArg()
    {
        return 'container';
    }

    public function allowedSubResources()
    {
        return ResourceAuthorizer::allowedSubResources('graphql', 'assets');
    }

    public function allowedFilters($args)
    {
        return FilterAuthorizer::allowedForSubResources('graphql', 'assets', $args['container']);
    }
}
