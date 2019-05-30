<?php

namespace Statamic\Data\Structures;

use Statamic\Data\Structures\Page;

class Pages
{
    protected $tree;
    protected $pages;
    protected $route;
    protected $parent;
    protected $prependParent = true;

    public function setTree(Tree $tree): self
    {
        $this->tree = $tree;

        return $this;
    }

    public function setPages(array $pages): self
    {
        $this->pages = $pages;

        return $this;
    }

    public function setRoute(string $route): self
    {
        $this->route = $route;

        return $this;
    }

    public function setParent(?Page $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function prependParent($prepend)
    {
        $this->prependParent = $prepend;

        return $this;
    }

    public function all()
    {
        $pages = collect($this->pages)->map(function ($branch) {
            $page = (new Page)
                ->setTree($this->tree)
                ->setParent($this->parent)
                ->setEntry($branch['entry'] ?? null)
                ->setUrl($branch['url'] ?? null)
                ->setTitle($branch['title'] ?? null)
                ->setChildren($branch['children'] ?? []);

            if ($this->route) {
                $page->setRoute($this->route);
            }

            return $page;
        });

        if ($this->prependParent && $this->parent) {
            $pages->prepend($this->parent);
        }

        return $pages;
    }

    public function flattenedPages()
    {
        $flattened = collect();

        foreach ($this->all() as $page) {
            $flattened->push($page);
            $flattened = $flattened->merge($page->flattenedPages());
        }

        return $flattened;
    }
}
