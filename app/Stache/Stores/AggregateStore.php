<?php

namespace Statamic\Stache\Stores;

use Statamic\Stache\Stache;
use Illuminate\Support\Facades\Cache;
use Statamic\Stache\Stores\ChildStore;

abstract class AggregateStore extends Store
{
    protected $stache;
    protected $stores;

    public function __construct(Stache $stache)
    {
        $this->stache = $stache;
        $this->stores = collect();
    }

    public function store($key)
    {
        if (! $this->stores->has($key)) {
            $this->stores->put($key, new ChildStore($this, $this->stache, $key));
        }

        return $this->stores->get($key);
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

    public function forEachSite($callback)
    {
        $this->stache->sites()->each(function ($site) use ($callback) {
            $callback($site, $this);
        });

        return $this;
    }
}
