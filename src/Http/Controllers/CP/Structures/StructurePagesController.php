<?php

namespace Statamic\Http\Controllers\CP\Structures;

use Illuminate\Http\Request;
use Statamic\Facades\Site;
use Statamic\Facades\Structure;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Structures\CollectionStructure;
use Statamic\Structures\Nav;
use Statamic\Structures\TreeBuilder;
use Statamic\Support\Arr;
use Statamic\Support\Str;

class StructurePagesController extends CpController
{
    public function index(Request $request, $handle)
    {
        $structure = Structure::find($handle);
        $site = $request->site ?? Site::selected()->handle();

        $pages = (new TreeBuilder)->buildForController([
            'structure' => $handle,
            'include_home' => true,
            'site' => $site,
        ]);

        return [
            'pages' => $pages,
            'syncableFields' => $this->getSyncableFields($structure), // todo: only needed for navs
        ];
    }

    private function getSyncableFields(Nav $nav)
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

    public function store(Request $request, $structure)
    {
        $structure = Structure::find($structure);

        if ($structure instanceof CollectionStructure) {
            return $this->storeCollection($structure, $request);
        }

        return $this->storeNav($structure, $request);
    }

    private function storeNav(Nav $nav, Request $request)
    {
        $tree = $this->toNavTree($request->pages);

        $nav
            ->in($request->site)
            ->tree($tree)
            ->save();
    }

    private function toNavTree($items)
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
                'children' => $this->toNavTree($item['children']),
            ]);
        })->all();
    }

    private function storeCollection(CollectionStructure $structure, Request $request)
    {
        $tree = $this->toCollectionTree($request->pages);

        $structure
            ->in($request->site)
            ->tree($tree)
            ->save();
    }

    private function toCollectionTree($items)
    {
        return collect($items)->map(function ($item) {
            return Arr::removeNullValues([
                'entry' => $ref = $item['id'] ?? null,
                'title' => $item['title'] ?? null,
                'url' => $ref ? null : ($item['url'] ?? null),
                'children' => $this->toCollectionTree($item['children']),
            ]);
        })->all();
    }
}
