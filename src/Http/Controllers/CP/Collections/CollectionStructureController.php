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
        $tree = $this->toTree($request->pages);

        collect($request->deletedEntries ?? [])
            ->map(function ($id) {
                Entry::find($id)->delete();
            });

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
                'children' => $this->toTree($item['children'])
            ]);
        })->all();
    }
}
