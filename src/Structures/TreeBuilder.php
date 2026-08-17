<?php

namespace Statamic\Structures;

use Statamic\Contracts\Structures\Nav;
use Statamic\Facades\Entry;
use Statamic\Facades\Structure;
use Statamic\Facades\Term;
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

        $entry = null;
        $fromPage = null;

        if ($from && $from !== '/') {
            if ($structure instanceof TaxonomyStructure) {
                $fromPage = $this->findTaxonomyPage($tree, $from, $params['site']);

                if (! $fromPage) {
                    return [];
                }
            } elseif (! $entry = Entry::findByUri(Str::start($from, '/'), $params['site'])) {
                return [];
            }
        }

        if ($fromPage) {
            $pages = $fromPage->pages()->all();
        } elseif ($entry) {
            $page = $tree->find($entry->id());
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

            if ($page->structure() instanceof TaxonomyStructure) {
                $this->hydrateTaxonomyPage($page, $params['site']);
            }

            return [
                'page' => $page->selectedQueryColumns($fields),
                'depth' => $depth,
                'children' => $this->toTree($page->pages()->all(), $params, $depth + 1),
            ];
        })->filter()->values()->all();
    }

    private function findTaxonomyPage($tree, string $from, string $site): ?Page
    {
        if ($term = Term::findByUri(Str::start($from, '/'), $site)) {
            return $tree->find($term->inDefaultLocale()->slug());
        }

        $slug = Str::afterLast(trim($from, '/'), '/');

        if ($page = $tree->find($slug)) {
            return $page;
        }

        $taxonomy = $tree->structure()->taxonomy();

        foreach ($tree->flattenedPages() as $page) {
            $term = Term::find($taxonomy->handle().'::'.$page->id());

            if ($term && $term->in($site)->slug() === $slug) {
                return $page;
            }
        }

        return null;
    }

    private function hydrateTaxonomyPage(Page $page, string $site): void
    {
        $term = Term::find($page->structure()->handle().'::'.$page->id())?->in($site);

        if (! $term) {
            return;
        }

        $page->setTitle($term->title());
        $page->setUrl($term->url());
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
            $collection = $page->mountedCollection();
            $referenceExists = $page->referenceExists();

            return [
                'id' => $page->id(),
                'entry' => $page->reference(),
                'title' => $page->hasCustomTitle() ? $page->title() : null,
                'entry_title' => $referenceExists ? $page->entry()->value('title') : null,
                'entry_blueprint' => $referenceExists ? [
                    'handle' => $page->entry()->blueprint()->handle(),
                    'title' => $page->entry()->blueprint()->title(),
                ] : null,
                'url' => $page->url(),
                'edit_url' => $page->editUrl(),
                'can_delete' => $referenceExists ? User::current()->can('delete', $page->entry()) : true,
                'slug' => $page->slug(),
                'status' => $referenceExists ? $page->status() : null,
                'redirect' => $referenceExists ? $page->entry()->get('redirect') : null,
                'collection' => ! $collection ? null : [
                    'handle' => $collection->handle(),
                    'title' => $collection->title(),
                    'edit_url' => $collection->showUrl(),
                    'create_url' => $collection->createEntryUrl(),
                ],
                'children' => (! empty($item['children'])) ? $this->transformTreeForController($item['children']) : [],
            ];
        })->values()->all();
    }
}
