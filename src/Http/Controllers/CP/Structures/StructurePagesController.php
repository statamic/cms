<?php

namespace Statamic\Http\Controllers\CP\Structures;

use Illuminate\Http\Request;
use Statamic\Facades\Site;
use Statamic\Facades\Structure;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Structures\TreeBuilder;
use Statamic\Support\Arr;

class StructurePagesController extends CpController
{
    public function index(Request $request, $structure)
    {
        $structure = Structure::find($structure);
        $site = $request->site ?? Site::selected()->handle();

        $pages = (new TreeBuilder)->buildForController([
            'structure' => $structure->handle(),
            'include_home' => true,
            'site' => $site,
        ]);

        return ['pages' => $pages];
    }

    public function store(Request $request, $structure)
    {
        $tree = $this->toTree($request->pages);

        Structure::find($structure)
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
