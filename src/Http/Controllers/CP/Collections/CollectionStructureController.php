<?php

namespace Statamic\Http\Controllers\CP\Collections;

use Illuminate\Http\Request;
use Statamic\Facades\Entry;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Support\Arr;

class CollectionStructureController extends CpController
{
    public function update(Request $request, $collection)
    {
        $this->authorize('reorder', $collection);

        $deletedEntries = collect(
            $request->deletedEntries ?? []
        )->map(function ($id) {
            return Entry::find($id);
        });

        if ($request->deleteLocalizationBehavior === 'copy') {
            $deletedEntries->each->detachLocalizations();
        } else {
            $deletedEntries->each->deleteDescendants();
        }

        $deletedEntries->each->delete();

        $tree = $this->toTree($request->pages);

        $collection
            ->structure()
            ->in($request->site)
            ->tree($tree)
            ->save();
    }

    protected function toTree($items)
    {
        return collect($items)->map(function ($item) {
            return Arr::removeNullValues([
                'entry' => $ref = $item['id'] ?? null,
                'title' => $ref ? null : ($item['title'] ?? null),
                'url' => $ref ? null : ($item['url'] ?? null),
                'children' => $this->toTree($item['children']),
            ]);
        })->all();
    }
}
