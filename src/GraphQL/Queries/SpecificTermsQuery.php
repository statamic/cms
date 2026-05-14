<?php

namespace Statamic\GraphQL\Queries;

use Facades\Statamic\API\FilterAuthorizer;
use GraphQL\Type\Definition\Type;
use Statamic\Facades\GraphQL;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Statamic\GraphQL\Middleware\AuthorizeFilters;
use Statamic\GraphQL\Middleware\ResolvePage;
use Statamic\GraphQL\Queries\Concerns\FiltersQuery;
use Statamic\GraphQL\Types\DynamicTermUnionType;
use Statamic\GraphQL\Types\JsonArgument;
use Statamic\GraphQL\Types\TermType;
use Statamic\Query\OrderBy;
use Statamic\Support\Str;

class SpecificTermsQuery extends Query
{
    use FiltersQuery;

    protected $middleware = [
        ResolvePage::class,
        AuthorizeFilters::class,
    ];

    public function __construct(protected string $taxonomyHandle)
    {
        $this->attributes['name'] = Str::camel($taxonomyHandle);

        parent::__construct();
    }

    public function type(): Type
    {
        $taxonomy = Taxonomy::findByHandle($this->taxonomyHandle);
        $blueprints = $taxonomy->termBlueprints();

        $combinations = $blueprints->map(fn ($blueprint) => [
            'taxonomy' => $taxonomy,
            'blueprint' => $blueprint,
        ])->values()->all();

        if (count($combinations) === 1) {
            $type = GraphQL::type(TermType::buildName($combinations[0]['taxonomy'], $combinations[0]['blueprint']));
        } else {
            $unionType = new DynamicTermUnionType($combinations);
            GraphQL::addType($unionType);
            $type = GraphQL::type($unionType->name);
        }

        return GraphQL::paginate($type);
    }

    public function args(): array
    {
        return [
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

        $query->where('taxonomy', $this->taxonomyHandle);

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

            if ($sort = OrderBy::column($sort)) {
                $query->orderBy($sort, $order);
            }
        }
    }

    public function allowedFilters($args)
    {
        return FilterAuthorizer::allowedForSubResources('graphql', 'taxonomies', $this->taxonomyHandle);
    }
}
