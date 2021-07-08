<?php

namespace Statamic\Http\Controllers\CP\Collections;

use Illuminate\Http\Request;
use Statamic\Contracts\Entries\Collection;
use Statamic\Facades\Site;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Structures\TreeBuilder;
use Statamic\Support\Arr;

class CollectionTreeController extends CpController
{
    public function index(Request $request, Collection $collection)
    {
        $site = $request->site ?? Site::selected()->handle();

        $pages = (new TreeBuilder)->buildForController([
            'structure' => $collection->structure(),
            'include_home' => true,
            'site' => $site,
        ]);

        return ['pages' => $pages];
    }

    public function update(Request $request, Collection $collection)
    {
        $tree = $this->toTree($request->pages);

        $collection
            ->structure()
            ->in($request->site)
            ->tree($tree)
            ->save();
    }

    private function toTree($items)
    {
        return collect($items)->map(function ($item) {
            return Arr::removeNullValues([
                'entry' => $ref = $item['id'] ?? null,
                'title' => $item['title'] ?? null,
                'url' => $ref ? null : ($item['url'] ?? null),
                'children' => $this->toTree($item['children']),
            ]);
        })->all();
    }
}
