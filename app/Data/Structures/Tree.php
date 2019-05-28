<?php

namespace Statamic\Data\Structures;

use Statamic\API;
use Statamic\API\Stache;
use Statamic\Data\ContainsData;
use Statamic\Data\ExistsAsFile;
use Statamic\FluentlyGetsAndSets;
use Statamic\Contracts\Data\Localization;

class Tree implements Localization
{
    use ExistsAsFile, FluentlyGetsAndSets;

    protected $locale;
    protected $route;
    protected $root;
    protected $tree = [];
    protected $withParent = true;
    protected $structure;

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
        return $this->fluentlyGetOrSet('tree')->args(func_get_args());
    }

    public function root($root = null)
    {
        return $this->fluentlyGetOrSet('root')->args(func_get_args());
    }

    public function sites()
    {
        $this->structure->sites();
    }

    public function handle()
    {
        return $this->structure->handle();
    }

    public function route($route = null)
    {
        return $this->fluentlyGetOrSet('route')->args(func_get_args());
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
            ->setEntry($this->root)
            ->setRoute($this->route())
            ->setRoot(true);
    }

    public function pages()
    {
        $pages = (new Pages)
            ->setTree($this->tree)
            ->setParent($this->parent())
            ->prependParent($this->withParent);

        if ($route = $this->route()) {
            $pages->setRoute($route);
        }

        return $pages;
    }

    public function flattenedPages()
    {
        return $this->pages()->flattenedPages();
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
            'route' => $this->route,
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
            'route' => $this->route,
            'root' => $this->root,
            'tree' => $this->tree,
        ];
    }

    public function append($entry)
    {
        $this->tree[] = ['entry' => $entry->id()];

        return $this;
    }
}
