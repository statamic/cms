<?php

namespace Statamic\Stache\Stores;

use Closure;
use Statamic\Stache\Stache;
use Illuminate\Filesystem\Filesystem;

abstract class AggregateStore extends Store
{
    protected $stache;
    protected $stores;
    protected $files;
    protected $childStoreCreator;

    public function __construct(Stache $stache, Filesystem $files)
    {
        $this->stache = $stache;
        $this->files = $files;
        $this->stores = collect();
    }

    public function store($key)
    {
        if (! $this->stores->has($key)) {
            $this->stores->put($key, $this->createChildStore($key));
        }

        return $this->stores->get($key);
    }

    protected function createChildStore($key)
    {
        $store = $this->childStoreCreator
            ? call_user_func($this->childStoreCreator)
            : app($this->childStore);

        return $store
            ->setChildKey($key)
            ->setParent($this);
    }

    public function setChildStoreCreator(Closure $callback)
    {
        $this->childStoreCreator = $callback;

        return $this;
    }

    public function stores()
    {
        return $this->stores;
    }

    public function childDirectory($child)
    {
        return $this->directory . $child->childKey();
    }

    public function getItem($key)
    {
        [$store, $id] = explode('::', $key, 2);

        return $this->store($store)->getItem($id);
    }

}
