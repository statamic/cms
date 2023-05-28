<?php

namespace Statamic\GraphQL\Queries;

use Facades\Statamic\API\FilterAuthorizer;
use GraphQL\Type\Definition\Type;
use Statamic\Facades\GraphQL;
use Statamic\Facades\User;
use Statamic\GraphQL\Middleware\AuthorizeFilters;
use Statamic\GraphQL\Middleware\ResolvePage;
use Statamic\GraphQL\Queries\Concerns\FiltersQuery;
use Statamic\GraphQL\Types\JsonArgument;
use Statamic\GraphQL\Types\UserType;
use Statamic\Support\Str;

class UsersQuery extends Query
{
    use FiltersQuery;

    protected $attributes = [
        'name' => 'users',
    ];

    protected $middleware = [
        ResolvePage::class,
        AuthorizeFilters::class,
    ];

    public function type(): Type
    {
        return GraphQL::paginate(GraphQL::type(UserType::NAME));
    }

    public function args(): array
    {
        return [
            'limit' => GraphQL::int(),
            'page' => GraphQL::int(),
            'filter' => GraphQL::type(JsonArgument::NAME),
            'sort' => GraphQL::listOf(GraphQL::string()),
        ];
    }

    public function resolve($root, $args)
    {
        $query = User::query();

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

    public function allowedFilters($args)
    {
        return collect(FilterAuthorizer::allowedForResource('graphql', 'users'))
            ->reject(fn ($field) => in_array($field, ['password', 'password_hash']))
            ->all();
    }
}
