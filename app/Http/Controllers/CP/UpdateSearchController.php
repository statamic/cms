<?php

namespace Statamic\Http\Controllers\CP;

use Statamic\API\Search;
use Illuminate\Http\Request;

class UpdateSearchController extends CpController
{
    public function index()
    {
        return view('statamic::utilities.search', [
            'indexes' => Search::indexes()
        ]);
    }

    public function update(Request $request)
    {
        $indexes = collect($request->validate([
            'indexes' => 'required',
        ])['indexes']);

        $indexes->each(function ($index) {
            Search::index($index)->update();
        });

        return back()->withSuccess('Indexes successfully updated.');
    }
}
