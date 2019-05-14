<?php

namespace Statamic\Http\Controllers\CP\Structures;

use Statamic\API\Site;
use Statamic\API\Structure;
use Illuminate\Http\Request;
use Statamic\Data\Structures\TreeBuilder;
use Statamic\Http\Controllers\CP\CpController;

class StructurePagesController extends CpController
{
    public function index(Request $request, $structure)
    {
        $structure = Structure::find($structure);
        $site = $request->site ?? Site::selected()->handle();

        $pages = (new TreeBuilder)->buildForController([
            'structure' => $structure->handle(),
            'include_home' => false,
            'site' => $site,
        ]);

        return ['pages' => $pages];
    }

    public function store(Request $request, $structure)
    {
        $tree = Structure::find($structure)
            ->in($request->site)
            ->tree($this->toTree($request->pages));

        $tree->save();
    }

    protected function toTree($items)
    {
        return collect($items)->map(function ($item) {
            return [
                'entry' => $item['id'],
                'children' => $this->toTree($item['children'])
            ];
        })->all();
    }
}
