<?php

namespace Statamic\Data\Structures;

use Statamic\API\Entry;
use Statamic\API\Structure as StructureAPI;
use Statamic\Contracts\Data\Structures\Structure as StructureContract;

class Structure implements StructureContract
{
    protected $handle;
    protected $data = [];

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
            ->setEntry($this->data['parent']);
    }

    public function save()
    {
        StructureAPI::save($this);
    }

    public function pages()
    {
        return (new Pages)
            ->setTree($this->data['tree'])
            ->setParentUri($this->parent()->uri())
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
}
