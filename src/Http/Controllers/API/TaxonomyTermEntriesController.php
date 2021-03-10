<?php

namespace Statamic\Http\Controllers\API;

use Statamic\Http\Resources\API\EntryResource;

class TaxonomyTermEntriesController extends ApiController
{
    protected $filterPublished = true;

    protected function abortIfDisabled()
    {
        // Abort if `taxonomies` resource is disabled
        $this->resourceConfigKey = 'taxonomies';
        $this->routeResourceKey = 'taxonomy';
        parent::abortIfDisabled();

        // Abort if `collections` resource is disabled
        $this->resourceConfigKey = 'collections';
        $this->routeResourceKey = false;
        parent::abortIfDisabled();
    }

    public function index($taxonomy, $term)
    {
        $this->abortIfDisabled();

        $query = $term->queryEntries();

        foreach ($this->allowedCollections() as $collection) {
            $query->where('collection', $collection);
        }

        return app(EntryResource::class)::collection(
            $this->filterSortAndPaginate($query)
        );
    }

    private function allowedCollections()
    {
        $entriesConfig = config('statamic.api.resources.collections');

        return is_array($entriesConfig) ? $entriesConfig : [];
    }
}
