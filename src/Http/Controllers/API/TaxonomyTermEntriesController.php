<?php

namespace Statamic\Http\Controllers\API;

use Statamic\Http\Resources\API\EntryResource;

class TaxonomyTermEntriesController extends ApiController
{
    protected function abortIfDisabled()
    {
        // Abort if `taxonomy-terms` endpoint is disabled
        $this->endpointConfigKey = 'taxonomy-terms';
        $this->limitRouteResource = 'taxonomy';
        parent::abortIfDisabled();

        // Abort if `abort` if endpoint is totally disabled
        $this->endpointConfigKey = 'entries';
        $this->limitRouteResource = false;
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
        $entriesConfig = config('statamic.api.endpoints.entries');

        return is_array($entriesConfig) ? $entriesConfig : [];
    }
}
