<?php

namespace Statamic\Http\Controllers\CP\Collections;

use Illuminate\Http\Request;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Resources\CP\Entries\Entry as EntryResource;

class PublishedEntriesController extends CpController
{
    use ExtractsFromEntryFields;

    public function store(Request $request, $collection, $entry)
    {
        $this->authorize('publish', $entry);

        $entry = $entry->publish([
            'message' => $request->message,
            'user' => User::fromUser($request->user()),
        ]);

        if ($entry === false) {
            return ['saved' => false];
        }

        $blueprint = $entry->blueprint();

        [$values] = $this->extractFromFields($entry, $blueprint);

        return [
            'data' => array_merge((new EntryResource($entry->fresh()))->resolve()['data'], [
                'values' => $values,
            ]),
            'saved' => true,
        ];
    }

    public function destroy(Request $request, $collection, $entry)
    {
        $this->authorize('publish', $entry);

        $entry = $entry->unpublish([
            'message' => $request->message,
            'user' => User::fromUser($request->user()),
        ]);

        if ($entry === false) {
            return ['saved' => false];
        }

        $blueprint = $entry->blueprint();

        [$values] = $this->extractFromFields($entry, $blueprint);

        return [
            'data' => array_merge((new EntryResource($entry->fresh()))->resolve()['data'], [
                'values' => $values,
            ]),
            'saved' => true,
        ];
    }
}
