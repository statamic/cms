<?php

namespace Statamic\Http\Controllers\CP\Collections;

use Statamic\API\Entry;
use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;

class PublishedEntriesController extends CpController
{
    public function store(Request $request, $collection, $entry)
    {
        $this->authorize('publish', $entry);

        $entry = $entry->publish([
            'message' => $request->message,
            'user' => $request->user(),
        ]);

        return $entry->toArray();
    }

    public function destroy(Request $request, $collection, $entry)
    {
        $this->authorize('publish', $entry);

        $entry->unpublish([
            'message' => $request->message,
            'user' => $request->user(),
        ]);
    }
}
