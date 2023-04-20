<?php

namespace Statamic\Http\Controllers\API;

use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\Entry;
use Statamic\Http\Resources\API\EntryResource;
use Statamic\Support\Str;

class CollectionEntriesController extends ApiController
{
    protected $resourceConfigKey = 'collections';
    protected $routeResourceKey = 'collection';
    protected $filterPublished = true;

    public function index($collection)
    {
        $this->abortIfDisabled();

        $with = $collection->entryBlueprints()
            ->flatMap(fn ($blueprint) => $blueprint->fields()->all())
            ->filter->isRelationship()->keys()->all();

        return app(EntryResource::class)::collection(
            $this->filterSortAndPaginate($collection->queryEntries()->with($with))
        );
    }

    public function show($collection, $handle)
    {
        $this->abortIfDisabled();

        $entry = Entry::find($handle);

        $this->abortIfInvalid($entry, $collection);
        $this->abortIfUnpublished($entry);

        return app(EntryResource::class)::make($entry);
    }

    protected function getFilters()
    {
        return parent::getFilters()->reject(fn ($_, $filter) => Str::startsWith($filter, 'taxonomy:'));
    }

    protected function filter($query)
    {
        parent::filter($query);

        collect(request()->filter ?? [])
            ->filter(fn ($_, $filter) => Str::startsWith($filter, 'taxonomy:'))
            ->each(fn ($value, $filter) => $this->applyTaxonomyFilter($query, $filter, $value));

        return $this;
    }

    protected function applyTaxonomyFilter($query, $filter, $terms)
    {
        [$_, $taxonomy, $condition] = array_pad(explode(':', $filter), 3, null);

        $terms = collect(explode(',', $terms))->map(fn ($term) => "$taxonomy::$term");

        if ($condition === 'in') {
            $query->whereTaxonomyIn($terms->all());
        } elseif ($condition === 'not_in') {
            $query->whereTaxonomyNotIn($terms->all());
        } else {
            $terms->each(fn ($term) => $query->whereTaxonomy($term));
        }
    }

    private function abortIfInvalid($entry, $collection)
    {
        if (! $entry || $entry->collection()->id() !== $collection->id()) {
            throw new NotFoundHttpException;
        }
    }
}
