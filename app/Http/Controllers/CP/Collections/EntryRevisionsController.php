<?php

namespace Statamic\Http\Controllers\CP\Collections;

use Statamic\API\Entry;
use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;

class EntryRevisionsController extends CpController
{
    public function index(Request $request, $collection, $id, $slug, $site)
    {
        if (! $entry = Entry::find($id)) {
            return $this->pageNotFound();
        }

        $entry = $entry->in($site);

        return $entry
            ->revisions()
            ->reverse()
            ->prepend($entry->workingCopy())
            ->filter()
            ->groupBy(function ($revision) {
                return $revision->date()->clone()->startOfDay()->format('U');
            })->map(function ($revisions, $day) {
                return compact('day', 'revisions');
            })->reverse()->values();
    }
}
