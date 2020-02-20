<?php

namespace Statamic\Http\Controllers\API;

use Illuminate\Http\Request;
use Statamic\Http\Resources\API\EntryResource;

class CollectionEntriesController extends ApiController
{
    public function index($collection, Request $request)
    {
        return app(EntryResource::class)::collection(
            $this->filterSortAndPaginate($collection->queryEntries())
        );
    }

    public function show($collection, $entry)
    {
        return app(EntryResource::class)::make($entry);
    }
}
