<?php

namespace Statamic\Http\Controllers\CP\Collections;

use Illuminate\Http\Request;
use Statamic\Facades\Site;
use Statamic\Http\Controllers\CP\CpController;

class ReorderEntriesController extends CpController
{
    public function __invoke(Request $request, $collection)
    {
        // Checked up front so an unauthorized user is denied before seeing any validation feedback.
        $this->authorize('reorder', $collection);

        $request->validate([
            'ids' => 'required|array',
            'page' => 'required|integer',
            'perPage' => 'required|integer',
            'site' => 'required',
        ]);

        abort_unless($site = Site::get($request->site), 404);

        $this->authorize('reorderInSite', [$collection, $site]);

        $tree = $collection->structure()->in($site->handle());

        $contents = collect($tree->tree())->keyBy('entry');

        $reorderPayload = $request->ids;

        if ($collection->sortDirection() === 'desc') {
            $reorderPayload = array_reverse($reorderPayload);
        }

        $reorderedEntries = clone $contents;

        $contents
            ->keys()
            ->forPage($request->page, $request->perPage)
            ->zip($reorderPayload)
            ->each(function ($operation) use ($contents, &$reorderedEntries) {
                $reorderedEntries->put(
                    $operation[0],
                    $contents->get($operation[1])
                );
            });

        $tree
            ->tree($reorderedEntries->values()->all())
            ->save();
    }
}
