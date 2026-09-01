<?php

namespace Statamic\Http\Controllers\API;

use Facades\Statamic\API\FilterAuthorizer;
use Facades\Statamic\API\QueryScopeAuthorizer;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\Entry;
use Statamic\Http\Resources\API\EntryResource;
use Statamic\Support\Str;
use Statamic\Tags\Concerns\QueriesTaxonomyTerms;

class CollectionEntriesController extends ApiController
{
    use QueriesTaxonomyTerms;

    protected $resourceConfigKey = 'collections';
    protected $routeResourceKey = 'collection';
    protected $filterPublished = true;
    protected $siteConstrained = true;
    protected $collectionHandle;

    public function index($collection)
    {
        $this->abortIfDisabled();

        $this->collectionHandle = $collection->handle();

        $with = $collection->entryBlueprints()
            ->flatMap(fn ($blueprint) => $blueprint->fields()->all())
            ->filter->isRelationship()->keys()->all();

        return app(EntryResource::class)::collection(
            $this->updateAndPaginate($collection->queryEntries()->with($with))
        );
    }

    public function show($collection, $handle)
    {
        $this->abortIfDisabled();

        $entry = $this->resolveEntry($collection, $handle);

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
        [$_, $taxonomy, $modifier] = array_pad(explode(':', $filter), 3, null);

        $values = collect(explode(',', $terms))->map(fn ($term) => "$taxonomy::$term");

        $this->queryTaxonomyTerms($query, $modifier, $values);
    }

    private function resolveEntry($collection, $handle)
    {
        if ($entry = Entry::find($handle)) {
            return $this->localize($entry);
        }

        $query = $collection->queryEntries()->where('slug', $handle);

        if ($site = $this->requestedSite()) {
            $query->where('site', $site);
        } elseif ($collection->sites()->count() > 1) {
            $query->where('site', $collection->sites()->first());
        }

        return $query->first();
    }

    private function abortIfInvalid($entry, $collection)
    {
        if (! $entry || $entry->collection()->id() !== $collection->id()) {
            throw new NotFoundHttpException;
        }
    }

    protected function allowedFilters()
    {
        return FilterAuthorizer::allowedForSubResources('api', 'collections', $this->collectionHandle);
    }

    protected function allowedQueryScopes()
    {
        return QueryScopeAuthorizer::allowedForSubResources('api', 'collections', $this->collectionHandle);
    }
}
