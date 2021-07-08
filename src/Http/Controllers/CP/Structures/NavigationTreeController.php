<?php

namespace Statamic\Http\Controllers\CP\Structures;

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
            $values = Arr::except($item['values'], ['title', 'url']);
            $values = Arr::only($values, $item['localizedFields']);
            $data = Arr::removeNullValues($values);

            return Arr::removeNullValues([
                'entry' => $ref = $item['id'] ?? null,
                'title' => in_array('title', $item['localizedFields']) ? $item['title'] : null,
                'url' => $ref ? null : ($item['url'] ?? null),
                'data' => $data,
                'children' => $this->toTree($item['children']),
            ]);
        })->all();
    }
}
