<?php

namespace Statamic\Http\Controllers\CP\Collections;

use Illuminate\Http\Request;
use Statamic\Facades\Entry;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Resources\CP\Entries\Entry as EntryResource;

class PublishedEntriesController extends CpController
{
    public function store(Request $request, $collection, $entry)
    {
        $this->authorize('publish', $entry);

        if ($collection->draftWithoutValidation()) {
            $fields = $entry
                ->blueprint()
                ->ensureField('published', ['type' => 'toggle'])
                ->fields()
                ->addValues($entry->data()->all());

            $fields
                ->validator()
                ->withRules(Entry::updateRules($collection, $entry))
                ->withReplacements([
                    'id' => $entry->id(),
                    'collection' => $collection->handle(),
                    'site' => $entry->locale(),
                ])->validate();
        }

        $entry = $entry->publish([
            'message' => $request->message,
            'user' => User::fromUser($request->user()),
        ]);

        return new EntryResource($entry);
    }

    public function destroy(Request $request, $collection, $entry)
    {
        $this->authorize('publish', $entry);

        $entry = $entry->unpublish([
            'message' => $request->message,
            'user' => User::fromUser($request->user()),
        ]);

        return new EntryResource($entry);
    }
}
