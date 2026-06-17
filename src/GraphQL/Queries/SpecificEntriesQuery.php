<?php

namespace Statamic\GraphQL\Queries;

use Facades\Statamic\API\FilterAuthorizer;
use Facades\Statamic\API\QueryScopeAuthorizer;
use GraphQL\Type\Definition\Type;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\GraphQL;
use Statamic\GraphQL\Middleware\AuthorizeFilters;
use Statamic\GraphQL\Middleware\AuthorizeQueryScopes;
use Statamic\GraphQL\Middleware\ResolvePage;
use Statamic\GraphQL\Queries\Concerns\FiltersQuery;
use Statamic\GraphQL\Queries\Concerns\ScopesQuery;
use Statamic\GraphQL\Types\DynamicEntryUnionType;
use Statamic\GraphQL\Types\JsonArgument;
use Statamic\Query\OrderBy;
use Statamic\Support\Str;

class SpecificEntriesQuery extends Query
{
    use FiltersQuery {
        filterQuery as traitFilterQuery;
    }

    use ScopesQuery;

    protected $middleware = [
        ResolvePage::class,
        AuthorizeFilters::class,
        AuthorizeQueryScopes::class,
    ];

    public function __construct(protected string $collectionHandle)
    {
        $this->attributes['name'] = Str::camel($collectionHandle);

        parent::__construct();
    }

    public function type(): Type
    {
        $collection = Collection::findByHandle($this->collectionHandle);

        return GraphQL::nonNull(GraphQL::paginate(DynamicEntryUnionType::createTypeFor($collection)));
    }

    public function args(): array
    {
        return [
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

        $query->whereIn('collection', DynamicEntryUnionType::collectionsFor(Collection::findByHandle($this->collectionHandle))->pluck('handle')->toArray());

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

            if ($sort = OrderBy::column($sort)) {
                $query->orderBy($sort, $order);
            }
        }
    }

    public function allowedFilters($args)
    {
        return FilterAuthorizer::allowedForSubResources('graphql', 'collections', $this->collectionHandle);
    }

    public function allowedScopes($args)
    {
        return QueryScopeAuthorizer::allowedForSubResources('graphql', 'collections', $this->collectionHandle);
    }
}
