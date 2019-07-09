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

        $order = $request->validate([
            'initial' => 'required|array',
            'new' => 'required|array',
        ]);

        $entries = $ids->mapWithKeys(function ($id) {
            $entry = Entry::find($id);
            return [$id => $entry];
        });

        $oldOrders = $entries->map->order()->sort()->values();

        foreach ($ids as $index => $id) {
            $entries[$id]->order($oldOrders[$index])->save();
        }
    }
}
