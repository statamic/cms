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
    protected $query;

    public function index($collection)
    {
        $this->abortIfDisabled();

        $with = $collection->entryBlueprints()
            ->flatMap(fn ($blueprint) => $blueprint->fields()->all())
            ->filter->isRelationship()->keys()->all();

        $this->query = $collection->queryEntries()->with($with);

        return app(EntryResource::class)::collection(
            $this->filterSortAndPaginate($this->query)
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
        return parent::getFilters()->filter(function ($value, $filter) {
            if (! Str::startsWith($filter, 'taxonomy:')) {
                return true;
            }

            $this->applyTaxonomyFilter($filter, $value);

            return false;
        });
    }

    protected function applyTaxonomyFilter($filter, $terms)
    {
        [$keyword, $taxonomy, $condition] = array_pad(explode(':', $filter), 3, null);

        $terms = collect($this->getPipedValues($terms))
            ->map(fn ($term) => "$taxonomy::$term");

        if ($condition === 'in') {
            $this->query->whereTaxonomyIn($terms->all());
        } elseif ($condition === 'not_in') {
            $this->query->whereTaxonomyNotIn($terms->all());
        } else {
            $terms->each(fn ($term) => $this->query->whereTaxonomy($term));
        }
    }

    private function abortIfInvalid($entry, $collection)
    {
        if (! $entry || $entry->collection()->id() !== $collection->id()) {
            throw new NotFoundHttpException;
        }
    }
}
