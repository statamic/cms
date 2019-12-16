<?php

namespace Statamic\Structures;

use Statamic\Facades;
use Statamic\Facades\Stache;
use Statamic\Data\ExistsAsFile;
use Statamic\Contracts\Data\Localization;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class Tree implements Localization
{
    use ExistsAsFile, FluentlyGetsAndSets;

    protected $locale;
    protected $root;
    protected $tree = [];
    protected $structure;
    protected $cachedFlattenedPages;

    public function locale($locale = null)
    {
        return $this->fluentlyGetOrSet('locale')->args(func_get_args());
    }

    public function structure($structure = null)
    {
        return $this->fluentlyGetOrSet('structure')->args(func_get_args());
    }

    public function tree($tree = null)
    {
        return $this->fluentlyGetOrSet('tree')
            ->setter(function ($tree) {
                return $this->validateTree($tree);
            })
            ->args(func_get_args());
    }

    public function root($root = null)
    {
        return $this->fluentlyGetOrSet('root')
            ->setter(function ($root) {
                return $this->validateRoot($root);
            })
            ->args(func_get_args());
    }

    public function sites()
    {
        $this->structure->sites();
    }

    public function handle()
    {
        return $this->structure->handle();
    }

    public function route()
    {
        if (! $collection = $this->structure->collection()) {
            return null;
        }

        return is_array($route = $collection->route())
            ? $route[$this->locale()]
            : $route;
    }

    public function path()
    {
        return vsprintf('%s/%s/%s.yaml', [
            rtrim(Stache::store('structures')->directory(), '/'),
            $this->locale(),
            $this->handle()
        ]);
    }

    public function parent()
    {
        if (!$this->root) {
            return null;
        }

        return (new Page)
            ->setTree($this)
            ->setEntry($this->root)
            ->setRoute($this->route())
            ->setDepth(1)
            ->setRoot(true);
    }

    public function pages()
    {
        $pages = (new Pages)
            ->setTree($this)
            ->setPages($this->tree)
            ->setParent($this->parent())
            ->setDepth(1);

        if ($route = $this->route()) {
            $pages->setRoute($route);
        }

        return $pages;
    }

    public function flattenedPages()
    {
        if ($this->cachedFlattenedPages) {
            return $this->cachedFlattenedPages;
        }

        return $this->cachedFlattenedPages = $this->pages()->flattenedPages();
    }

    public function uris()
    {
        return $this->flattenedPages()->map->uri();
    }

    public function page(string $id): ?Page
    {
        return $this->flattenedPages()
            ->filter->reference()
            ->keyBy->reference()
            ->get($id);
    }

    public function save()
    {
        $this
            ->structure()
            ->addTree($this)
            ->save();
    }

    public function fileData()
    {
        return [
            'root' => $this->root,
            'tree' => $this->removeEmptyChildren($this->tree),
        ];
    }

    protected function removeEmptyChildren($array)
    {
        return collect($array)->map(function ($item) {
            $item['children'] = $this->removeEmptyChildren(array_get($item, 'children', []));

            if (empty($item['children'])) {
                unset($item['children']);
            }

            return $item;
        })->all();
    }

    public function editUrl()
    {
        return cp_route('structures.show', ['structure' => $this->handle(), 'site' => $this->locale()]);
    }

    public function toCacheableArray()
    {
        return [
            'path' => $this->initialPath() ?? $this->path(),
            'root' => $this->root,
            'tree' => $this->tree,
        ];
    }

    public function append($entry)
    {
        $this->tree[] = ['entry' => $entry->id()];

        return $this;
    }

    public function appendTo($parent, $page)
    {
        if (! $this->page($parent)) {
            throw new \Exception("Page [{$parent}] does not exist in this structure");
        }

        if (is_string($page)) {
            $page = ['entry' => $page];
        } elseif (is_object($page)) {
            $page = ['entry' => $page->id()];
        }

        $this->tree = $this->appendToInBranches($parent, $page, $this->tree);

        return $this;
    }

    private function appendToInBranches($parent, $page, $branches)
    {
        foreach ($branches as &$branch) {
            $children = $branch['children'] ?? [];

            if ($branch['entry'] === $parent) {
                $children[] = $page;
                $branch['children'] = $children;
                break;
            }

            $children = $this->appendToInBranches($parent, $page, $children);

            if (! empty($children)) {
                $branch['children'] = $children;
            }
        }

        return $branches;
    }

    public function move($entry, $target)
    {
        if ($this->page($entry)->parent()->id() === $target) {
            return $this;
        }

        [$match, $branches] = $this->removeFromInBranches($entry, $this->tree);

        $this->tree = $branches;

        return $this->appendTo($target, $match);
    }

    private function removeFromInBranches($entry, $branches)
    {
        $match = null;

        foreach ($branches as $key => &$branch) {
            if ($branch['entry'] === $entry) {
                $match = $branch;
                unset($branches[$key]);
                break;
            }

            [$m, $children] = $this->removeFromInBranches($entry, $branch['children'] ?? []);

            if ($m) {
                $match = $m;
            }

            if (empty($children)) {
                unset($branch['children']);
            } else {
                $branch['children'] = $children;
            }
        }

        return [$match, array_values($branches)];
    }

    protected function validateRoot($root)
    {
        if (! $this->structure->isCollectionBased()) {
            return $root;
        }

        if ($entryId = $this->getEntryIdsFromTree($this->tree)->duplicates()->first()) {
            $this->throwDuplicateEntryException($entryId);
        }

        return $root;
    }

    protected function validateTree($tree)
    {
        if (! $this->structure->isCollectionBased()) {
            return $tree;
        }

        $entryIds = $this->getEntryIdsFromTree($tree);

        if ($this->root) {
            $entryIds->push($this->root);
        }

        if ($entryId = $entryIds->duplicates()->first()) {
            $this->throwDuplicateEntryException($entryId);
        }

        return $tree;
    }

    private function throwDuplicateEntryException($id)
    {
        throw new \Exception("Duplicate entry [{$id}] in [{$this->structure->handle()}] structure.");
    }

    protected function getEntryIdsFromTree($tree)
    {
        return collect($tree)
            ->map(function ($item) {
                return [
                    'entry' => $item['entry'] ?? null,
                    'children' => isset($item['children']) ? $this->getEntryIdsFromTree($item['children']) : null
                ];
            })
            ->flatten()
            ->filter();
    }
}
