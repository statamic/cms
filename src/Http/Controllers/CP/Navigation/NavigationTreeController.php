<?php

namespace Statamic\Http\Controllers\CP\Navigation;

use Illuminate\Http\Request;
use Statamic\Facades\Nav;
use Statamic\Facades\Site;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Structures\TreeBuilder;
use Statamic\Support\Arr;
use Statamic\Support\Str;

class NavigationTreeController extends CpController
{
    public function index(Request $request, $handle)
    {
        $nav = Nav::find($handle);

        $site = $request->site ?? Site::selected()->handle();

        $nav->in($site)->ensureBranchIds();

        $pages = (new TreeBuilder)->buildForController([
            'structure' => $nav,
            'include_home' => true,
            'site' => $site,
        ]);

        return [
            'pages' => $pages,
            'syncableFields' => $this->getSyncableFields($nav),
        ];
    }

    private function getSyncableFields($nav)
    {
        $navFields = $nav->blueprint()->fields()->all()->keys();

        return $nav->collections()->flatMap(function ($collection) {
            return $collection->entryBlueprints();
        })->keyBy(function ($blueprint) {
            return Str::after($blueprint->namespace(), 'collections.').'.'.$blueprint->handle();
        })->map(function ($blueprint) use ($navFields) {
            return $blueprint->fields()->all()->keys()->intersect($navFields)->values()->all();
        })->all();
    }

    public function update(Request $request, $structure)
    {
        $structure = Nav::find($structure);

        $tree = $this->toTree($request->pages);

        $structure
            ->in($request->site)
            ->tree($tree)
            ->save();
    }

    private function toTree($items)
    {
        return collect($items)->map(function ($item) {
            $data = Arr::only($item['values'], $item['localizedFields']);

            return Arr::removeNullValues([
                'entry' => $item['id'] ?? null,
                'title' => Arr::pull($data, 'title'),
                'url' => Arr::pull($data, 'url'),
                'data' => $data,
                'children' => $this->toTree($item['children']),
            ]);
        })->all();
    }
}
