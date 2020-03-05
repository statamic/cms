<?php

namespace Statamic\Structures;

use Statamic\Support\Arr;
use Statamic\Facades\Structure;

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

        $pages = $tree->pages()
            ->prependParent(Arr::get($params, 'include_home'))
            ->all();

        return $this->toTree($pages, 1);
    }

    protected function toTree($pages, $depth)
    {
        return $pages->map(function ($page) use ($depth) {
            if ($page->reference() && !$page->referenceExists()) {
                return null;
            }

            return [
                'page' => $page,
                'depth' => $depth,
                'children' => $this->toTree($page->pages()->all(), $depth + 1)
            ];
        })->filter()->values()->all();
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
            $collection = $page->collection();

            return [
                'id'          => $page->id(),
                'title'       => $page->title(),
                'url'         => $page->url(),
                'edit_url'    => $page->editUrl(),
                'slug'        => $page->slug(),
                'redirect'    => $page->reference() ? $page->entry()->get('redirect') : null,
                'collection'  => !$collection ? null : [
                    'handle' => $collection->handle(),
                    'title' => $collection->title(),
                    'edit_url' => $collection->showUrl(),
                    'create_url' => $collection->createEntryUrl(),
                ],
                'children'    => (! empty($item['children'])) ? $this->transformTreeForController($item['children']) : []
            ];
        })->values()->all();
    }
}
