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

        $entries = collect($order['initial'])->mapWithKeys(function ($id) {
            $entry = Entry::find($id);
            return [$id => $entry];
        });

        $initialOrderPositions = $entries->map(function ($entry) use ($collection) {
            return $collection->getEntryPosition($entry->id());
        })->values();

        foreach ($order['new'] as $index => $id) {
            $entries[$id]->order($initialOrderPositions[$index])->save();
        }
    }
}
