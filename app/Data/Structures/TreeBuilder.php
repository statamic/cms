<?php

namespace Statamic\Data\Structures;

use Statamic\API\Structure;

class TreeBuilder
{
    public function build($params)
    {
        if (! $structure = Structure::find($params['structure'])) {
            return null;
        }

        if (! $tree = $structure->in($params['site'])) {
            return null;
        }

        if (!$params['include_home']) {
            $tree->withoutParent();
        }

        return $this->toTree($tree->pages()->all(), 1);
    }

    protected function toTree($pages, $depth)
    {
        return $pages->keyBy->uri()->map(function ($page) use ($depth) {
            return [
                'page' => $page,
                'depth' => $depth,
                'children' => $this->toTree($page->pages()->all(), $depth + 1)
            ];
        })->all();
    }

    public function buildForController($params)
    {
        $tree = $this->build($params);

        return $this->transformTreeForController($tree);
    }

    protected function transformTreeForController($tree)
    {
        return collect($tree)->map(function ($item) {
            $page = $item['page'];

            return [
                'id'          => $page->id(),
                'title'       => (string) $page->value('title'),
                'url'         => $page->url(),
                'edit_url'    => $page->editUrl(),
                'slug'        => $page->slug(),
                'children'    => (! empty($item['children'])) ? $this->transformTreeForController($item['children']) : []
            ];
        })->values()->all();
    }
}
