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

        $entry = $entry->in($site)->published(true);

        return $this->createRevisionAndSave($entry, $request);
    }

    public function destroy(Request $request, $collection, $id, $slug, $site)
    {
        if (! $entry = Entry::find($id)) {
            return $this->pageNotFound();
        }

        $entry = $entry->in($site)->published(false);

        return $this->createRevisionAndSave($entry, $request);
    }

    protected function createRevisionAndSave($entry, $request)
    {
        $entry
            ->makeRevision()
            ->user($request->user())
            ->message($request->message)
            ->save();

        $entry->save();
    }
}
