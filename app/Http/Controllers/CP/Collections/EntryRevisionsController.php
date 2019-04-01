<?php

namespace Statamic\Http\Controllers\CP\Collections;

use Statamic\API\Entry;
use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;

class EntryRevisionsController extends CpController
{
    public function index(Request $request, $collection, $entry)
    {
        $revisions = $entry
            ->revisions()
            ->reverse()
            ->prepend($this->workingCopy($entry))
            ->filter();

        // The first non manually created revision would be considered the "current"
        // version. It's what corresponds to what's in the content directory.
        optional($revisions->first(function ($revision) {
            return $revision->action() != 'revision';
        }))->attribute('current', true);

        return $revisions
            ->groupBy(function ($revision) {
                return $revision->date()->clone()->startOfDay()->format('U');
            })->map(function ($revisions, $day) {
                return compact('day', 'revisions');
            })->reverse()->values();
    }

    public function store(Request $request, $collection, $entry)
    {
        $entry->createRevision([
            'message' => $request->message,
            'user' => $request->user(),
        ]);
    }

    protected function workingCopy($entry)
    {
        if ($entry->published()) {
            return $entry->workingCopy();
        }

        return $entry
            ->makeWorkingCopy()
            ->date($entry->lastModified())
            ->user($entry->lastModifiedBy());
    }
}
