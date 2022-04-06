<?php

namespace Statamic\Http\Controllers\CP\Collections;

use Illuminate\Http\Request;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\Collection;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Resources\CP\Entries\Entry as EntryResource;

class PublishedEntriesController extends CpController
{
    public function store(Request $request, $collection, $entry)
    {
        $handle = $collection;
        $collection = Collection::findByHandle($collection);
        if (! $collection) {
            throw new NotFoundHttpException("Collection [$handle] not found.");
        }

        $this->authorize('publish', $entry);

        $entry = $entry->publish([
            'message' => $request->message,
            'user' => User::fromUser($request->user()),
        ]);

        return new EntryResource($entry);
    }

    public function destroy(Request $request, $collection, $entry)
    {
        $handle = $collection;
        $collection = Collection::findByHandle($collection);
        if (! $collection) {
            throw new NotFoundHttpException("Collection [$handle] not found.");
        }

        $this->authorize('publish', $entry);

        $entry = $entry->unpublish([
            'message' => $request->message,
            'user' => User::fromUser($request->user()),
        ]);

        return new EntryResource($entry);
    }
}
