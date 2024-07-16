<?php

namespace Statamic\GraphQL\Queries;

use Facades\Statamic\API\FilterAuthorizer;
use Facades\Statamic\API\ResourceAuthorizer;
use GraphQL\Type\Definition\Type;
use Statamic\Facades\GraphQL;
use Statamic\Facades\Term;
use Statamic\GraphQL\Middleware\AuthorizeFilters;
use Statamic\GraphQL\Middleware\AuthorizeSubResources;
use Statamic\GraphQL\Middleware\ResolvePage;
use Statamic\GraphQL\Queries\Concerns\FiltersQuery;
use Statamic\GraphQL\Types\JsonArgument;
use Statamic\GraphQL\Types\TermInterface;
use Statamic\Support\Str;

class TermsQuery extends Query
{
    use FiltersQuery;

    protected $attributes = [
        'name' => 'terms',
    ];

    protected $middleware = [
        AuthorizeSubResources::class,
        ResolvePage::class,
        AuthorizeFilters::class,
    ];

    public function type(): Type
    {
        return GraphQL::paginate(GraphQL::type(TermInterface::NAME));
    }

    public function args(): array
    {
        return [
            'taxonomy' => GraphQL::listOf(GraphQL::string()),
            'limit' => GraphQL::int(),
            'page' => GraphQL::int(),
            'filter' => GraphQL::type(JsonArgument::NAME),
            'sort' => GraphQL::listOf(GraphQL::string()),
            'site' => GraphQL::string(),
        ];
    }

    public function resolve($root, $args)
    {
        $query = Term::query();

        $query->whereIn('taxonomy', $args['taxonomy'] ?? $this->allowedSubResources());

        if ($filters = $args['filter'] ?? null) {
            $this->filterQuery($query, $filters);
        }

        if ($sort = $args['sort'] ?? null) {
            $this->sortQuery($query, $sort);
        }

        if ($site = $args['site'] ?? null) {
            $query->where('site', $site);
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
        return 'taxonomy';
    }

    public function allowedSubResources()
    {
        return ResourceAuthorizer::allowedSubResources('graphql', 'taxonomies');
    }

    public function allowedFilters($args)
    {
        return FilterAuthorizer::allowedForSubResources('graphql', 'taxonomies', $args['taxonomy'] ?? '*');
    }
}
