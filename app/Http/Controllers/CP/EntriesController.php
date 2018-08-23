<?php

namespace Statamic\Http\Controllers\CP;

use Statamic\API\Entry;
use Illuminate\Http\Request;

class EntriesController extends CpController
{
    public function index($collection)
    {
        // TODO: Bring over the rest of the logic.
        return Entry::whereCollection($collection)->toArray();
    }

    public function edit(Request $request, $collection, $slug)
    {
        $entry = Entry::findBySlug($slug, $collection);

        $this->authorize('view', $entry);

        return view('statamic::entries.edit', [
            'entry' => $entry,
            'readOnly' => $request->user()->cant('edit', $entry)
        ]);
    }

    public function update($slug)
    {

    }

    public function create()
    {
        return view('statamic::entries.create');
    }

    public function store()
    {

    }

    public function destroy($slug)
    {

    }
}
