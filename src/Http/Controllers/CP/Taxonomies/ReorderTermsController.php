<?php

namespace Statamic\Http\Controllers\CP\Taxonomies;

use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Support\Str;

class ReorderTermsController extends CpController
{
    public function __invoke(Request $request, $taxonomy)
    {
        $this->authorize('reorder', $taxonomy);

        abort_unless($taxonomy->orderable(), 403);

        $request->validate([
            'ids' => 'required|array',
            'page' => 'required|integer',
            'perPage' => 'required|integer',
            'site' => 'required',
        ]);

        $tree = $taxonomy->structure()->tree();

        $contents = collect($tree->tree())->keyBy('term');

        $reorderPayload = collect($request->ids)
            ->map(fn ($id) => Str::after($id, '::'))
            ->all();

        if ($taxonomy->sortDirection() === 'desc') {
            $reorderPayload = array_reverse($reorderPayload);
        }

        $reorderedTerms = clone $contents;

        $contents
            ->keys()
            ->forPage($request->page, $request->perPage)
            ->zip($reorderPayload)
            ->each(function ($operation) use ($contents, &$reorderedTerms) {
                $reorderedTerms->put(
                    $operation[0],
                    $contents->get($operation[1])
                );
            });

        $tree
            ->tree($reorderedTerms->values()->all())
            ->save();
    }
}
