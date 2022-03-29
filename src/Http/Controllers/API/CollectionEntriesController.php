<?php

namespace Statamic\Http\Controllers\API;

use Statamic\Facades\Entry;
use Statamic\Exceptions\NotFoundHttpException;
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
        throw_if(
            ! ($entry = Entry::find($handle)) || $entry->collection()->id() !== $collection->id(),
            new NotFoundHttpException
        );

        $this->abortIfDisabled();
        $this->abortIfUnpublished($entry);

        return app(EntryResource::class)::make($entry);
    }
}
