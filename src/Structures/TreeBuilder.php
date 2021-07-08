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
        $showUnpublished = $params['show_unpublished'] ?? true;

        if ($maxDepth && $depth > $maxDepth) {
            return [];
        }

        return $pages->map(function ($page) use ($fields, $params, $depth, $showUnpublished) {
            if ($page->reference() && ! $page->referenceExists()) {
                return null;
            } elseif (! $showUnpublished && $page->entry() && $page->entry()->status() !== 'published') {
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

            // TODO: Refactor? This is only relevant to navs.
            if ($blueprint = $page->blueprint()) {
                [$values, $meta] = $this->extractFromFields($page, $blueprint);

                if ($entry = $page->entry()) {
                    [$originValues, $originMeta] = $this->extractFromFields($entry, $blueprint);
                }
            }

            return [
                'id'          => $page->id(),
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

                // only needed for navs
                'entryCollection' => $page->referenceExists() ? $page->entry()->collectionHandle() : null,
                'entryBlueprint' => $page->referenceExists() ? $page->entry()->blueprint()->handle() : null,
                'values' => $values ?? null,
                'meta' => $meta ?? null,
                'originValues' => $originValues ?? null,
                'originMeta' => $originMeta ?? null,
                'localizedFields' => $page->pageData()->keys()
                    ->when($page->hasCustomTitle(), function ($keys) {
                        return $keys->push('title');
                    })->unique()->all(),

            ];
        })->values()->all();
    }

    private function extractFromFields($page, $blueprint)
    {
        $values = $page instanceof Page
            ? $this->getPageValues($page)
            : $this->getEntryValues($page);

        $fields = $blueprint
            ->ensureField('title', [])
            ->ensureField('url', [])
            ->fields()
            ->addValues($values)
            ->preProcess();

        $values = $fields->values();

        return [$values->all(), $fields->meta()];
    }

    private function getPageValues($page)
    {
        $entryValues = ($entry = $page->entry())
            ? $this->getEntryValues($entry)
            : collect();

        return collect($entryValues)
            ->merge($page->pageData())
            ->merge([
                'title' => $page->title(),
                'url' => $page->reference() ? null : $page->url(),
            ])->all();
    }

    private function getEntryValues($entry)
    {
        // todo: we dont need all the entry data, just the ones that also exist in the page blueprint.

        // The values should only be data merged with the origin data.
        // We don't want injected collection values, which $entry->values() would have given us.
        $target = $entry;
        $values = $target->data();
        while ($target->hasOrigin()) {
            $target = $target->origin();
            $values = $target->data()->merge($values);
        }

        return $values->all();
    }
}
