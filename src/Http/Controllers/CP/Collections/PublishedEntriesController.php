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

        $publish = $entry->publish([
            'message' => $request->message,
            'user' => User::fromUser($request->user()),
        ]);

        $saved = is_object($publish);
        $entry = $saved ? $publish : $entry;

        $blueprint = $entry->blueprint();

        [$values] = $this->extractFromFields($entry, $blueprint);

        return [
            'data' => array_merge((new EntryResource($entry->fresh()))->resolve()['data'], [
                'values' => $values,
            ]),
            'saved' => $saved,
        ];
    }

    public function destroy(Request $request, $collection, $entry)
    {
        $this->authorize('publish', $entry);

        $unpublish = $entry->unpublish([
            'message' => $request->message,
            'user' => User::fromUser($request->user()),
        ]);

        $saved = is_object($unpublish);
        $entry = $saved ? $unpublish : $entry;

        $blueprint = $entry->blueprint();

        [$values] = $this->extractFromFields($entry, $blueprint);

        return [
            'data' => array_merge((new EntryResource($entry->fresh()))->resolve()['data'], [
                'values' => $values,
            ]),
            'saved' => $saved,
        ];
    }
}
