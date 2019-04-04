<?php

namespace Statamic\Stache\Stores;

use Closure;
use Statamic\Stache\Stache;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Cache;
use Statamic\Stache\Stores\ChildStore;
use Statamic\Stache\Exceptions\StoreExpiredException;

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

    public function getStoreById($id)
    {
        if (! $store = $this->getIdMap()->get($id)) {
            return null;
        }

        $store = explode('::', $store)[1];

        return $this->store($store);
    }

    public function setPaths($paths)
    {
        foreach ($paths as $site => $sitePaths) {
            foreach ($sitePaths as $key => $path) {
                $this->setSitePath($site, $key, $path);
            }
        }

        return $this;
    }

    public function setSitePath($site, $key, $path)
    {
        list($store, $id) = $this->extractKeys($key);

        $this->store($store)->setSitePath($site, $id, $path);

        return $this;
    }

    public function getIdFromPath($path, $site = null)
    {
        foreach ($this->stores() as $store) {
            if ($match = $store->getIdFromPath($path, $site)) {
                return $match;
            }
        }
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

    public function markAsUpdated()
    {
        $this->stores->each->markAsUpdated();

        return $this;
    }

    public function load()
    {
        $this->stores->each->load();
    }

    public function isUpdated()
    {
        foreach ($this->stores as $store) {
            if ($store->isUpdated()) {
                return true;
            }
        }

        return false;
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

    public function getIdFromUri($uri, $site = null)
    {
        foreach ($this->stores() as $store) {
            if ($match = $store->getIdFromUri($uri, $site)) {
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
    }

    public function cacheMetaKeys()
    {
        Cache::forever($this->getMetaKeysCacheKey(), $this->stores->reject->isExpired()->keys()->all());
    }

    public function cacheHasMeta()
    {
        return Cache::get($this->getMetaKeysCacheKey());
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

    public function insert($item, $key)
    {
        if (str_contains($key, '::')) {
            list($store, $id) = $this->extractKeys($key);
        } else {
            $store = $key;
            $id = $item->id();
        }

        $this->store($store)->insert($item, $id);

        return $this;
    }

    public function remove($item)
    {
        list(, $store) = $this->extractKeys($this->getIdMap()->get($item));

        $this->store($store)->remove($item);

        return $this;
    }

    // TODO: Test this.
    // There's an equivalent test for the BasicStore.
    public function removeByPath($path)
    {
        if (! $id = $this->getIdFromPath($path)) {
            // If there's no ID, the deleted path may actually have been a renamed
            // one, which would have already been updated in the Stache.
            return $this;
        }

        list(, $store) = $this->extractKeys($this->getIdMap()->get($id));

        $this->store($store)->removeByPath($path);

        return $this;
    }
}
