<?php

namespace Statamic\GraphQL\Queries;

use Facades\Statamic\API\FilterAuthorizer;
use Facades\Statamic\API\ResourceAuthorizer;
use Facades\Statamic\GraphQL\Middleware\AuthorizeFilters;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\ValidationException;
use Statamic\Facades;
use Statamic\Facades\GraphQL;
use Statamic\GraphQL\Middleware\AuthorizeSubResources;
use Statamic\GraphQL\Queries\Concerns\FiltersQuery;
use Statamic\GraphQL\Types\EntryInterface;
use Statamic\GraphQL\Types\JsonArgument;

class EntryQuery extends Query
{
    use FiltersQuery {
        filterQuery as traitFilterQuery;
    }

    protected $attributes = [
        'name' => 'entry',
    ];

    protected $middleware = [
        AuthorizeSubResources::class,
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

        $filters = $args['filter'] ?? null;

        $this->filterQuery($query, $filters);

        $entry = $query->limit(1)->get()->first();

        // The `AuthorizeSubResources` middleware will authorize when using `collection` arg,
        // but this is still required when the user queries entry using other args.
        if ($entry && ! in_array($collection = $entry->collection()->handle(), $this->allowedSubResources())) {
            throw ValidationException::withMessages([
                'collection' => 'Forbidden: '.$collection,
            ]);
        }

        // We cannot use the `AuthorizeFilters` middleware on this query, because
        // you can get an entry by `id`, `slug`, `uri`, etc. so we'll get the
        // queried entry's collection and authorize filters manually here.
        if ($entry && $filters) {
            $allowedFilters = collect($this->allowedFilters([
                'collection' => $entry->collection()->handle(),
            ]));

            $forbidden = collect($filters)
                ->keys()
                ->filter(fn ($filter) => ! $allowedFilters->contains($filter));

            if ($forbidden->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'filter' => 'Forbidden: '.$forbidden->join(', '),
                ]);
            }
        }

        return $entry;
    }

    private function filterQuery($query, $filters)
    {
        if (! request()->isLivePreview() && (! isset($filters['status']) && ! isset($filters['published']))) {
            $filters['status'] = 'published';
        }

        $this->traitFilterQuery($query, $filters);
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
}
