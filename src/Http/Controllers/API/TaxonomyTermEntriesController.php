<?php

namespace Statamic\Http\Controllers\API;

use Statamic\Http\Resources\API\EntryResource;

class TaxonomyTermEntriesController extends ApiController
{
    public function index($taxonomy, $term)
    {
        return app(EntryResource::class)::collection(
            $this->filterSortAndPaginate($term->queryEntries())
        );
    }
}
