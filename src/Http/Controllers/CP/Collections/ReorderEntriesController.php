<?php

namespace Statamic\Http\Controllers\CP\Collections;

use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;

use function Statamic\trans as __;

class ReorderEntriesController extends CpController
{
    public function __invoke(Request $request, $collection)
    {
        $this->authorize('reorder', $collection);

        $request->validate([
            'ids' => 'required|array',
            'page' => 'required|integer',
            'perPage' => 'required|integer',
            'site' => 'required',
        ]);

        $tree = $collection->structure()->in($request->site);

        $branches = collect($tree->tree())->keyBy('entry');

        // A descending collection lists the tree in reverse. Work in the order the
        // listing was in, then flip it back before saving.
        $descending = $collection->sortDirection() === 'desc';

        $ids = $descending
            ? $branches->keys()->reverse()->values()
            : $branches->keys();

        $offset = ($request->page - 1) * $request->perPage;

        $submitted = collect($request->ids)->map(fn ($id) => (string) $id);
        $current = $ids->slice($offset, $request->perPage)->map(fn ($id) => (string) $id);

        // If the submitted ids aren't a rearrangement of the page being reordered, the
        // listing was out of date. Continuing would duplicate and drop entries.
        if ($submitted->sort()->values()->all() !== $current->sort()->values()->all()) {
            abort(409, __('statamic::messages.collection_entries_reorder_out_of_date'));
        }

        $reordered = $ids->values()->all();

        foreach ($request->ids as $index => $id) {
            $reordered[$offset + $index] = $id;
        }

        $reordered = collect($reordered);

        if ($descending) {
            $reordered = $reordered->reverse();
        }

        $tree
            ->tree($reordered->map(fn ($id) => $branches->get($id))->values()->all())
            ->save();
    }
}
