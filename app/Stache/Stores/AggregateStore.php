<?php

namespace Statamic\Stache\Stores;

use Closure;
use Statamic\Stache\Stache;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Cache;
use Statamic\Stache\Stores\ChildStore;

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
            : app(ChildStore::class);

        return $store
            ->setKey($key)
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

    public function setPaths($paths)
    {
        collect($paths)->each(function ($path, $key) {
            $this->setPath($key, $path);
        });

        return $this;
    }

    public function setPath($key, $path)
    {
        list($store, $id) = $this->extractKeys($key);

        $this->store($store)->setPath($id, $path);

        return $this;
    }

    public function getIdMap()
    {
        return $this->stores->mapWithKeys(function ($store) {
            return $store->getIdMap();
        });
    }

    protected function extractKeys($string)
    {
        return explode('::', $string);
    }

    public function isLoaded()
    {
        foreach ($this->stores as $store) {
            if (! $store->isLoaded()) {
                return false;
            }
        }

        return true;
    }

    public function markAsLoaded()
    {
        $this->stores->each->markAsLoaded();

        return $this;
    }

    public function load()
    {
        $this->stores->each->load();
    }

    public function getItems()
    {
        return $this->stores->map(function ($store) {
            return $store->getItems();
        });
    }

    public function getItemsWithoutLoading()
    {
        return $this->stores->map(function ($store) {
            return $store->getItemsWithoutLoading();
        });
    }

    public function setItem($key, $item)
    {
        list($store, $id) = $this->extractKeys($key);

        $this->store($store)->setItem($id, $item);

        return $this;
    }

    public function getSiteUri($site, $key)
    {
        list($store, $id) = $this->extractKeys($key);

        return $this->store($store)->getSiteUri($site, $id);
    }

    public function setSiteUri($site, $key, $uri)
    {
        list($store, $id) = $this->extractKeys($key);

        $this->store($store)->setSiteUri($site, $id, $uri);

        return $this;
    }

    public function getIdFromUri($uri)
    {
        foreach ($this->stores() as $store) {
            if ($match = $store->getIdFromUri($uri)) {
                return $match;
            }
        }
    }

    public function forEachSite($callback)
    {
        $this->stache->sites()->each(function ($site) use ($callback) {
            $callback($site, $this);
        });

        return $this;
    }

    public function cache()
    {
        $this->stores->each->cache();

        Cache::forever($this->getMetaKeysCacheKey(), $this->stores->keys()->all());
    }

    public function getMetaFromCache()
    {
        $keys = Cache::get($this->getMetaKeysCacheKey());

        return collect($keys)->mapWithKeys(function ($store) {
            return $this->store($store)->getMetaFromCache();
        })->all();
    }

    protected function getMetaKeysCacheKey()
    {
        return 'stache::meta/' . $this->key() . '-keys';
    }

    public function insert($item, $key, $path = null)
    {
        if (str_contains($key, '::')) {
            list($store, $id) = $this->extractKeys($key);
        } else {
            $store = $key;
            $id = $item->id();
        }

        $this->store($store)->insert($item, $id, $path);

        return $this;
    }
}
