<?php

namespace Statamic\Structures;

use Statamic\Contracts\Structures\Nav;
use Statamic\Facades\Entry;
use Statamic\Facades\Structure;
use Statamic\Facades\User;
use Statamic\Support\Arr;
use Statamic\Support\Str;

class TreeBuilder
{
    public function build($params)
    {
        if ($params['structure'] instanceof \Statamic\Contracts\Structures\Structure) {
            $structure = $params['structure'];
        } elseif (! $structure = Structure::find($params['structure'])) {
            return null;
        }

        $from = $params['from'] ?? null;

        if ($from && $structure instanceof Nav) {
            throw new \Exception('Cannot get a nested starting position on a navigation structure.');
        }

        if (! $tree = $structure->in($params['site'])) {
            return null;
        }

        $tree->withEntries();

        $entry = ($from && $from !== '/') ? Entry::findByUri(Str::start($from, '/'), $params['site']) : null;

        if ($entry) {
            $page = $tree->page($entry->id());
            $pages = $page->pages()->all();
        } else {
            $pages = $tree->pages()
                ->prependParent(Arr::get($params, 'include_home'))
                ->all();
        }

        return $this->toTree($pages, $params);
    }

    protected function toTree($pages, $params, $depth = 1)
    {
        $maxDepth = $params['max_depth'] ?? null;
        $fields = $params['fields'] ?? null;
        $query = $params['query'] ?? null;

        if ($maxDepth && $depth > $maxDepth) {
            return [];
        }

        if ($query && empty($pages = $query->withItems($pages)->get())) {
            return [];
        }

        return $pages->map(function ($page) use ($fields, $params, $depth) {
            if ($page->reference() && ! $page->referenceExists()) {
                return null;
            }

            return [
                'page' => $page->selectedQueryColumns($fields),
                'depth' => $depth,
                'children' => $this->toTree($page->pages()->all(), $params, $depth + 1),
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
                'entry'       => $page->reference(),
                'title'       => $page->hasCustomTitle() ? $page->title() : null,
                'entry_title' => $page->referenceExists() ? $page->entry()->value('title') : null,
                'url'         => $page->referenceExists() ? null : $page->url(),
                'edit_url'    => $page->editUrl(),
                'can_delete'  => $page->referenceExists() ? User::current()->can('delete', $page->entry()) : true,
                'slug'        => $page->slug(),
                'status'      => $page->referenceExists() ? $page->status() : null,
                'redirect'    => $page->referenceExists() ? $page->entry()->get('redirect') : null,
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
