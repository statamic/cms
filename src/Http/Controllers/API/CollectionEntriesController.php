<?php

namespace Statamic\Http\Controllers\API;

use Illuminate\Http\Request;
use Statamic\API\Cacher;
use Statamic\Http\Resources\API\EntryResource;

class CollectionEntriesController extends ApiController
{
    public function index($collection, Request $request)
    {
        return app(Cacher::class)->remember($request, function () use ($collection) {
            return app(EntryResource::class)::collection(
                $this->filterSortAndPaginate($collection->queryEntries())
            );
        });
    }

    public function show($collection, $entry)
    {
        return app(Cacher::class)->remember($request, function () use ($entry) {
            return app(EntryResource::class)::make($entry);
        });
    }
}
