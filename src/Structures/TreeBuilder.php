<?php

namespace Statamic\Structures;

use Statamic\Contracts\Structures\Nav;
use Statamic\Facades\Entry;
use Statamic\Facades\Structure;
use Statamic\Support\Arr;
use Statamic\Support\Str;

class TreeBuilder
{
    public function build($params)
    {
        if (! $structure = Structure::find($params['structure'])) {
            return null;
        }

        $from = $params['from'] ?? null;
        $maxDepth = $params['max_depth'] ?? null;
        $fields = $params['fields'] ?? null;

        if ($from && $structure instanceof Nav) {
            throw new \Exception('Cannot get a nested starting position on a navigation structure.');
        }

        if (! $tree = $structure->in($params['site'])) {
            return null;
        }

        $entry = ($from && $from !== '/') ? Entry::findByUri(Str::start($from, '/'), $params['site']) : null;

        if ($entry) {
            $page = $tree->page($entry->id());
            $pages = $page->pages()->all();
        } else {
            $pages = $tree->pages()
                ->prependParent(Arr::get($params, 'include_home'))
                ->all();
        }

        return $this->toTree($pages, 1, $maxDepth, $fields);
    }

    protected function toTree($pages, $depth, $maxDepth, $fields)
    {
        if ($maxDepth && $depth > $maxDepth) {
            return [];
        }

        return $pages->map(function ($page) use ($depth, $maxDepth, $fields) {
            if ($page->reference() && ! $page->referenceExists()) {
                return null;
            }

            return [
                'page' => $page->selectedQueryColumns($fields),
                'depth' => $depth,
                'children' => $this->toTree($page->pages()->all(), $depth + 1, $maxDepth, $fields),
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
                'collection'  => ! $collection ? null : [
                    'handle' => $collection->handle(),
                    'title' => $collection->title(),
                    'edit_url' => $collection->showUrl(),
                    'create_url' => $collection->createEntryUrl(),
                ],
                'children'    => (! empty($item['children'])) ? $this->transformTreeForController($item['children']) : [],
            ];
        })->values()->all();
    }
}
