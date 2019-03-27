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

        return $this->createRevisionAndSave($entry->in($site), $request, function ($entry) {
            $entry->published(true);
        });
    }

    public function destroy(Request $request, $collection, $id, $slug, $site)
    {
        if (! $entry = Entry::find($id)) {
            return $this->pageNotFound();
        }

        return $this->createRevisionAndSave($entry->in($site), $request, function ($entry) {
            $entry->published(false);
        });
    }

    protected function createRevisionAndSave($entry, $request, $callback)
    {
        $entry = $entry->fromWorkingCopy();

        $callback($entry);

        $entry->save();

        $entry
            ->makeRevision()
            ->user($request->user())
            ->message($request->message ?? false)
            ->save();

        $entry->workingCopy()->delete();
    }
}
