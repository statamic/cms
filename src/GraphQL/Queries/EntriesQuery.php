<?php

namespace Statamic\GraphQL\Queries;

use Facades\Statamic\API\FilterAuthorizer;
use Facades\Statamic\API\QueryScopeAuthorizer;
use Facades\Statamic\API\ResourceAuthorizer;
use GraphQL\Type\Definition\Type;
use Statamic\Facades\Entry;
use Statamic\Facades\GraphQL;
use Statamic\GraphQL\Middleware\AuthorizeFilters;
use Statamic\GraphQL\Middleware\AuthorizeQueryScopes;
use Statamic\GraphQL\Middleware\AuthorizeSubResources;
use Statamic\GraphQL\Middleware\ResolvePage;
use Statamic\GraphQL\Queries\Concerns\FiltersQuery;
use Statamic\GraphQL\Queries\Concerns\ScopesQuery;
use Statamic\GraphQL\Types\EntryInterface;
use Statamic\GraphQL\Types\JsonArgument;
use Statamic\Support\Str;

class EntriesQuery extends Query
{
    use FiltersQuery {
        filterQuery as traitFilterQuery;
    }

    use ScopesQuery;

    protected $attributes = [
        'name' => 'entries',
    ];

    protected $middleware = [
        AuthorizeSubResources::class,
        ResolvePage::class,
        AuthorizeFilters::class,
        AuthorizeQueryScopes::class,
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
            'query_scope' => GraphQL::type(JsonArgument::NAME),
            'sort' => GraphQL::listOf(GraphQL::string()),
            'site' => GraphQL::string(),
        ];
    }

    public function resolve($root, $args)
    {
        $query = Entry::query();

        $query->whereIn('collection', $args['collection'] ?? $this->allowedSubResources());

        if ($site = $args['site'] ?? null) {
            $query->where('site', $site);
        }

        $this->filterQuery($query, $args['filter'] ?? []);

        $this->scopeQuery($query, $args['query_scope'] ?? []);

        $this->sortQuery($query, $args['sort'] ?? []);

        return $query->paginate($args['limit'] ?? 1000);
    }

    private function filterQuery($query, $filters)
    {
        if (! isset($filters['status']) && ! isset($filters['published'])) {
            $filters['status'] = 'published';
        }

        $this->traitFilterQuery($query, $filters);
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

    public function subResourceArg()
    {
        return 'collection';
    }

    public function allowedSubResources()
    {
        return ResourceAuthorizer::allowedSubResources('graphql', 'collections');
    }

    public function allowedFilters($args)
    {
        return FilterAuthorizer::allowedForSubResources('graphql', 'collections', $args['collection'] ?? '*');
    }

    public function allowedScopes($args)
    {
        return QueryScopeAuthorizer::allowedForSubResources('graphql', 'collections', $args['collection'] ?? '*');
    }
}
