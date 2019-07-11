<?php

namespace Statamic\Http\Controllers\CP\Collections;

use Statamic\API\Entry;
use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;

class ReorderEntriesController extends CpController
{
    public function __invoke(Request $request, $collection)
    {
        $this->authorize('reorder', $collection);

        $request->validate(['ids' => 'required|array']);

        $entries = collect($request->ids)->mapWithKeys(function ($id) {
            return [$id => Entry::find($id)];
        });

        $initialOrderPositions = $entries->map->order()->sort()->values();

        foreach ($request->ids as $index => $id) {
            $entries[$id]->order($initialOrderPositions[$index])->save();
        }
    }
}
