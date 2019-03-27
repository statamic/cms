<?php

namespace Statamic\Http\Controllers\CP\Collections;

use Statamic\API\Entry;
use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;

class PublishedEntriesController extends CpController
{
    public function store(Request $request, $collection, $id, $slug, $site)
    {
        if (! $entry = Entry::find($id)) {
            return $this->pageNotFound();
        }

        $entry->publish([
            'message' => $request->message,
            'user' => $request->user(),
        ]);
    }

    public function destroy(Request $request, $collection, $id, $slug, $site)
    {
        if (! $entry = Entry::find($id)) {
            return $this->pageNotFound();
        }

        $entry->unpublish([
            'message' => $request->message,
            'user' => $request->user(),
        ]);
    }
}
