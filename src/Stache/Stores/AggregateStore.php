<?php

namespace Statamic\Stache\Stores;

use Closure;

abstract class AggregateStore extends Store
{
    protected $stores;
    protected $childStoreCreator;

    public function __construct()
    {
        $this->stores = collect();
    }

    protected function getIndexedValues($name, $only)
    {
        // The keys are provided as an array of IDs. It's faster to do has() than contains() so we'll flip them.
        $only = $only->flip();

        // Return a map of the requested keys to the corresponding indexed values.
        return $this->stores()
            ->mapWithKeys(fn ($store) => $store->resolveIndex($name)->load()->items())
            ->where(fn ($value, $key) => $only->has($key));
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
        return $this->directory.$child->childKey();
    }

    public function getItems($keys)
    {
        return collect($keys)->map(function ($key) {
            return $this->getItem($key);
        });
    }

    public function getItem($key)
    {
        [$store, $id] = explode('::', $key, 2);

        return $this->store($store)->getItem($id);
    }

    public function clear()
    {
        $this->discoverStores()->each->clear();
    }

    public function warm()
    {
        $this->discoverStores()->each->warm();
    }

    public function paths()
    {
        return $this->discoverStores()->flatMap(function ($store) {
            return $store->paths();
        });
    }

    abstract public function discoverStores();
}
