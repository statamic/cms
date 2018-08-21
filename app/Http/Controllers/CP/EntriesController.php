<?php

namespace Statamic\Http\Controllers\CP;

use Statamic\API\Entry;

class EntriesController extends CpController
{
    public function index($collection)
    {
        // TODO: Bring over the rest of the logic.
        return Entry::whereCollection($collection)->toArray();
    }

    public function edit($collection, $slug)
    {
        $entry = Entry::findBySlug($slug, $collection);

        return view('statamic::entries.edit', compact('entry'));
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
