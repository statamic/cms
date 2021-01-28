<?php

namespace Statamic\Http\Controllers\API;

use Statamic\Http\Resources\API\EntryResource;

class CollectionEntriesController extends ApiController
{
    public function index($collection)
    {
        return $this->withCache(function () use ($collection) {
            return app(EntryResource::class)::collection(
                $this->filterSortAndPaginate($collection->queryEntries())
            );
        });
    }

    public function show($collection, $entry)
    {
        return $this->withCache(function () use ($entry) {
            return app(EntryResource::class)::make($entry);
        });
    }
}
