<?php

namespace Statamic\GraphQL\Queries;

use Facades\Statamic\API\FilterAuthorizer;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\ValidationException;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\GraphQL;
use Statamic\GraphQL\Queries\Concerns\FiltersQuery;
use Statamic\GraphQL\Types\DynamicEntryUnionType;
use Statamic\GraphQL\Types\JsonArgument;
use Statamic\Support\Str;

class SpecificEntryQuery extends Query
{
    use FiltersQuery {
        filterQuery as traitFilterQuery;
    }

    public function __construct(protected string $collectionHandle)
    {
        $name = Str::camel(Str::singular($collectionHandle));

        if ($name === Str::camel($collectionHandle)) {
            $name .= 'Entry';
        }

        $this->attributes['name'] = $name;

        parent::__construct();
    }

    public function type(): Type
    {
        return DynamicEntryUnionType::createTypeFor(Collection::findByHandle($this->collectionHandle));
    }

    public function args(): array
    {
        return [
            'id' => GraphQL::string(),
            'slug' => GraphQL::string(),
            'uri' => GraphQL::string(),
            'site' => GraphQL::string(),
            'filter' => GraphQL::type(JsonArgument::NAME),
        ];
    }

    public function resolve($root, $args)
    {
        $query = Entry::query();

        $query->whereIn('collection', DynamicEntryUnionType::collectionsFor(Collection::findByHandle($this->collectionHandle))->pluck('handle')->toArray());

        if ($id = $args['id'] ?? null) {
            $query->where('id', $id);
        }

        if ($slug = $args['slug'] ?? null) {
            $query->where('slug', $slug);
        }

        if ($uri = $args['uri'] ?? null) {
            $query->where('uri', $uri);
        }

        if ($site = $args['site'] ?? null) {
            $query->where('site', $site);
        }

        $filters = $args['filter'] ?? [];

        $this->filterQuery($query, $filters);

        $entry = $query->limit(1)->get()->first();

        if ($entry && $entry->status() !== 'published' && request()->isLivePreview() && ! request()->isLivePreviewOf($entry)) {
            return null;
        }

        if ($entry && $filters) {
            $allowedFilters = collect($this->allowedFilters([]));

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

    public function allowedFilters($args)
    {
        return FilterAuthorizer::allowedForSubResources('graphql', 'collections', $this->collectionHandle);
    }
}
