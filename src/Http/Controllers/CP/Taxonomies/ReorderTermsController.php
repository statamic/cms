<?php

namespace Statamic\Http\Controllers\CP\Taxonomies;

use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Support\Str;

use function Statamic\trans as __;

class ReorderTermsController extends CpController
{
    public function __invoke(Request $request, $taxonomy)
    {
        $this->authorize('reorder', $taxonomy);

        abort_unless($taxonomy->orderable(), 403);

        $request->validate([
            'ids' => 'required|array',
            // Without min:1, page 0 gives a negative offset, which slices from the end
            // of the tree and then writes negative keys onto it.
            'page' => 'required|integer|min:1',
            'perPage' => 'required|integer|min:1',
            'site' => 'required',
        ]);

        $tree = $taxonomy->structure()->tree();

        $branches = collect($tree->tree())->keyBy('term');

        // A descending taxonomy lists the tree in reverse. Work in the order the
        // listing was in, then flip it back before saving.
        $descending = $taxonomy->sortDirection() === 'desc';

        $slugs = $descending
            ? $branches->keys()->reverse()->values()
            : $branches->keys();

        $offset = ($request->page - 1) * $request->perPage;

        $submitted = collect($request->ids)->map(fn ($id) => Str::after($id, '::'))->values();
        $current = $slugs->slice($offset, $request->perPage)->values();

        // If the submitted ids aren't a rearrangement of the page being reordered, the
        // listing was out of date. Continuing would duplicate and drop terms.
        if ($submitted->sort()->values()->all() !== $current->sort()->values()->all()) {
            abort(409, __('statamic::messages.taxonomy_terms_reorder_out_of_date'));
        }

        $reordered = $slugs->values()->all();

        foreach ($submitted as $index => $slug) {
            $reordered[$offset + $index] = $slug;
        }

        $reordered = collect($reordered);

        if ($descending) {
            $reordered = $reordered->reverse();
        }

        $tree
            ->tree($reordered->map(fn ($slug) => $branches->get($slug))->values()->all())
            ->save();
    }
}
