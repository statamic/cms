<?php

namespace Statamic\Structures;

use Statamic\Facades;
use Statamic\Facades\Stache;
use Statamic\Data\ContainsData;
use Statamic\Data\ExistsAsFile;
use Statamic\Contracts\Data\Localization;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class Tree implements Localization
{
    use ExistsAsFile, FluentlyGetsAndSets;

    protected $locale;
    protected $root;
    protected $tree = [];
    protected $withParent = true;
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
            ->setDepth(1)
            ->prependParent($this->withParent);

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

    public function withoutParent()
    {
        $this->withParent = false;

        return $this;
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
