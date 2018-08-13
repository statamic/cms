<?php

namespace Statamic\Data\Structures;

use Statamic\Data\Structures\Page;

class Pages
{
    protected $tree;
    protected $route;
    protected $parentUri;

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

    public function setParentUri(string $uri): self
    {
        $this->parentUri = $uri;

        return $this;
    }

    public function all()
    {
        return collect($this->tree)->keyBy('entry')->map(function ($branch) {
            return (new Page)
                ->setParentUri($this->parentUri)
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
