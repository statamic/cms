<?php

namespace Statamic\Http\Controllers\API;

use Statamic\Http\Resources\API\EntryResource;

class CollectionEntriesController extends ApiController
{
    protected $resourceConfigKey = 'collections';
    protected $routeResourceKey = 'collection';

    public function index($collection)
    {
        $this->abortIfDisabled();

        return app(EntryResource::class)::collection(
            $this->filterSortAndPaginate($collection->queryEntries())
        );
    }

    public function show($collection, $entry)
    {
        $this->abortIfDisabled();

        return app(EntryResource::class)::make($entry);
    }
}
