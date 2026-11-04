<?php

namespace Statamic\Http\Controllers\CP\Utilities;

use Illuminate\Http\Request;
use Statamic\Events\SearchIndexUpdated;
use Statamic\Facades\Search;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Support\Str;

use function Statamic\trans as __;

class UpdateSearchController extends CpController
{
    public function update(Request $request)
    {
        $indexes = collect($request->validate([
            'indexes' => 'required',
        ])['indexes']);

        $availableIndexes = Search::indexes();

        $indexes->each(function ($index) use ($availableIndexes) {
            $name = Str::before($index, '::');

            $index = $availableIndexes->get($name) ?? throw new \InvalidArgumentException("Search index [{$name}] is not defined.");

            $index->update();

            SearchIndexUpdated::dispatch($index);
        });

        return back()->withSuccess(__('Update successful.'));
    }
}
