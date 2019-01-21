<?php

namespace Statamic\Data\Structures;

use Statamic\API\Entry;
use Statamic\API\Stache;
use Statamic\API\Structure as StructureAPI;
use Statamic\Contracts\Data\Structures\Structure as StructureContract;

class Structure implements StructureContract
{
    protected $handle;
    protected $data = [];
    protected $withParent = true;

    public function handle($handle = null)
    {
        if (is_null($handle)) {
            return $this->handle;
        }

        $this->handle = $handle;

        return $this;
    }

    public function data($data = null)
    {
        if (is_null($data)) {
            return $this->data;
        }

        $this->data = $data;

        return $this;
    }

    public function title($title = null)
    {
        if (is_null($title)) {
            return array_get($this->data, 'title', ucfirst($this->handle()));
        }

        $this->data['title'] = $title;

        return $this;
    }

    public function route($route = null)
    {
        if (is_null($route)) {
            return array_get($this->data, 'route');
        }

        $this->data['route'] = $route;

        return $this;
    }

    public function parent()
    {
        return (new Page)
            ->setEntry($this->data['root']);
    }

    public function save()
    {
        StructureAPI::save($this);
    }

    public function pages()
    {
        $tree = $this->data['tree'];

        if ($this->withParent) {
            array_unshift($tree, ['entry' => $this->data['root']]);
        }

        return (new Pages)
            ->setTree($tree)
            ->setParent($this->withParent ? $this->parent() : null)
            ->setRoute($this->route());
    }

    public function flattenedPages()
    {
        return $this->pages()->flattenedPages();
    }

    public function uris()
    {
        return $this->flattenedPages()->map->uri();
    }

    public function toCacheableArray()
    {
        return $this->data;
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

    public function path()
    {
        return vsprintf('%s/%s.yaml', [
            rtrim(Stache::store('structures')->directory(), '/'),
            $this->handle
        ]);
    }
}
