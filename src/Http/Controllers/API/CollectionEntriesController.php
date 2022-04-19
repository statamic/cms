<?php

namespace Statamic\Http\Controllers\API;

use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\Entry;
use Statamic\Http\Resources\API\EntryResource;

class CollectionEntriesController extends ApiController
{
    protected $resourceConfigKey = 'collections';
    protected $routeResourceKey = 'collection';
    protected $filterPublished = true;

    public function index($collection)
    {
        $this->abortIfDisabled();

        $with = $collection->entryBlueprints()
            ->flatMap(fn ($blueprint) => $blueprint->fields()->all())
            ->filter->isRelationship()->keys()->all();

        return app(EntryResource::class)::collection(
            $this->filterSortAndPaginate($collection->queryEntries()->with($with))
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

    private function abortIfInvalid($entry, $collection)
    {
        if (! $entry || $entry->collection()->id() !== $collection->id()) {
            throw new NotFoundHttpException;
        }
    }
}
