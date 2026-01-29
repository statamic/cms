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
        $keys = collect($keys);

        if ($keys->isEmpty()) {
            return collect();
        }

        // Group keys by child store for batch fetching
        $grouped = $keys->mapWithKeys(function ($key) {
            [$store, $id] = explode('::', $key, 2);

            return [$key => compact('store', 'id')];
        })->groupBy('store');

        // Batch fetch from each child store
        $fetched = $grouped->flatMap(function ($items, $store) {
            $ids = $items->pluck('id');
            $storeItems = $this->store($store)->getItems($ids);

            // Re-key with full keys (store::id)
            return $ids->mapWithKeys(fn ($id, $i) => ["{$store}::{$id}" => $storeItems[$i]]);
        });

        // Return in original order
        return $keys->map(fn ($key) => $fetched[$key]);
    }

    public function getItem($key)
    {
        [$store, $id] = explode('::', $key, 2);

        return $this->store($store)->getItem($id);
    }

    public function getItemValues($keys, $valueIndex, $keyIndex)
    {
        return $keys
            ->map(function ($key) {
                [$store, $key] = explode('::', $key, 2);

                return compact('store', 'key');
            })
            ->groupBy('store')
            ->flatMap(fn ($items, $store) => $this->store($store)->getItemValues($items->map->key, $valueIndex, $keyIndex));
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
