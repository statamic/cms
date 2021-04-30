<?php

namespace Statamic\Stache;

use Illuminate\Support\Facades\Cache;
use Statamic\Stache\Stores\AggregateStore;
use Statamic\Stache\Stores\Store;

class Duplicates
{
    protected $stache;
    protected $items = [];

    public function __construct(Stache $stache)
    {
        $this->stache = $stache;
    }

    public function all()
    {
        return collect($this->items)->map(function ($items, $store) {
            return collect($items)->map(function ($paths, $id) use ($store) {
                return array_merge([
                    $this->stache->store($store)->getItem($id)->path(),
                ], $paths);
            })->all();
        });
    }

    public function track(Store $store, $id, $path)
    {
        $duplicates = $this->items[$store->key()] ?? [];

        if (in_array($path, $duplicates[$id] ?? [])) {
            return;
        }

        $duplicates[$id][] = $path;

        $this->items[$store->key()] = $duplicates;
    }

    public function save()
    {
        Cache::forever('stache::duplicates', $this->items);
    }

    public function load()
    {
        $this->items = Cache::get('stache::duplicates', []);

        return $this;
    }

    public function setItems($items)
    {
        $this->items = $items;

        return $this;
    }

    public function getItems()
    {
        return $this->items;
    }

    public function find()
    {
        $this->stache->stores()->flatMap(function ($store) {
            return $store instanceof AggregateStore ? $store->discoverStores() : [$store];
        })->each(function ($store) {
            $store->clearCachedPaths();
            $store->paths();
        });

        return $this;
    }

    public function has($path)
    {
        foreach ($this->items as $store => $duplicates) {
            foreach ($duplicates as $id => $paths) {
                if (collect($paths)->contains($path)) {
                    return true;
                }
            }
        }

        return false;
    }
}
