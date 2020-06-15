<?php

namespace Statamic\Http\Controllers\API;

use Illuminate\Http\Request;
use Statamic\Http\Resources\API\EntryResource;

class TaxonomyTermEntriesController extends ApiController
{
    public function index($taxonomy, $term, Request $request)
    {
        return app(EntryResource::class)::collection(
            $this->filterSortAndPaginate($term->queryEntries())
        );
    }
}
