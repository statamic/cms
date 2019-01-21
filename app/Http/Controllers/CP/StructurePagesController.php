<?php

namespace Statamic\Http\Controllers\CP;

use Statamic\API\Structure;
use Illuminate\Http\Request;

class StructurePagesController extends CpController
{
    public function index($structure)
    {
        $structure = Structure::find($structure);

        $tree = (new \Statamic\Addons\Nav\ContentTreeBuilder)->build([
            'structure' => $structure->handle(),
            'include_home' => false
        ]);

        $data = $this->transformTree($tree);

        return ['pages' => $data];
    }

    public function store(Request $request, $structure)
    {
        $structure = Structure::find($structure);

        $structure->data(array_merge($structure->data(), [
            'tree' => $this->toTree($request->pages)
        ]))->save();
    }

    protected function transformTree($tree)
    {
        return collect($tree)->map(function ($item) {
            $page = $item['page'];

            return [
                'id'          => $page->id(),
                'title'       => (string) $page->get('title'),
                'url'         => $page->url(),
                'slug'        => $page->slug(),
                'children'    => (! empty($item['children'])) ? $this->transformTree($item['children']) : []
            ];
        })->values()->all();
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
