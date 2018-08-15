<?php

namespace Statamic\Data\Structures;

use Statamic\Data\Structures\Page;

class Pages
{
    protected $tree;
    protected $route;
    protected $parent;

    public function setTree(array $tree): self
    {
        $this->tree = $tree;

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

    public function all()
    {
        return collect($this->tree)->keyBy('entry')->map(function ($branch) {
            return (new Page)
                ->setParent($this->parent)
                ->setRoute($this->route)
                ->setEntry($branch['entry'])
                ->setChildren($branch['children'] ?? []);
        });
    }

    public function uris()
    {
        return $this->all()->map->uri();
    }

    public function flattenedPages()
    {
        $flattened = collect();

        foreach ($this->all() as $id => $page) {
            $flattened->put($id, $page);
            $flattened = $flattened->merge($page->flattenedPages());
        }

        return $flattened;
    }
}
