<?php

namespace Statamic\Data\Structures;

use Statamic\Data\Localization;
use Statamic\Data\ExistsAsFile;
use Statamic\Data\ContainsData;
use Statamic\Contracts\Data\Localization as LocalizationContract;

class Tree implements LocalizationContract
{
    use Localization, ExistsAsFile;

    protected $route;
    protected $root;
    protected $tree = [];
    protected $withParent = true;
    protected $structure;

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
            $this->locale,
            $this->handle
        ]);
    }

    public function parent()
    {
        return (new Page)
            ->setEntry($this->root)
            ->setRoute($this->route())
            ->setRoot(true);
    }

    public function pages()
    {
        return (new Pages)
            ->setTree($this->tree)
            ->setParent($this->parent())
            ->setRoute($this->route())
            ->prependParent($this->withParent);
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
        return $this->flattenedPages()->get($id);
    }

    public function withoutParent()
    {
        $this->withParent = false;

        return $this;
    }
}
