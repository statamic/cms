<?php

namespace Statamic\Stache\Stores;

use Statamic\Stache\Stache;
use Illuminate\Support\Facades\Cache;

abstract class BasicStore extends Store
{
    protected $stache;
    protected $paths;
    protected $uris;
    protected $items;
    protected $loaded = false;

    public function __construct(Stache $stache)
    {
        $this->stache = $stache;

        $this->paths = collect();
        $this->uris = collect();
        $this->items = collect();

        $this->forEachSite(function ($site) {
            $this->setSiteUris($site, []);
        });
    }

    public function forEachSite($callback)
    {
        $this->stache->sites()->each(function ($site) use ($callback) {
            $callback($site, $this);
        });

        return $this;
    }

    public function getPaths()
    {
        return $this->paths;
    }

    public function setPaths($paths)
    {
        $this->paths = collect($paths);

        return $this;
    }

    public function getPath($key)
    {
        return $this->paths->get($key);
    }

    public function setPath($key, $path)
    {
        $this->paths->put($key, $path);

        return $this;
    }

    public function getSiteUri($site, $key)
    {
        return $this->uris->get($site)->get($key);
    }

    public function setSiteUri($site, $key, $uri)
    {
        $this->uris->get($site)->put($key, $uri);

        return $this;
    }

    public function getSiteUris($site)
    {
        return $this->uris->get($site);
    }

    public function setSiteUris($site, $uris)
    {
        $this->uris->put($site, collect($uris));

        return $this;
    }

    public function getUris()
    {
        return $this->uris;
    }

    public function setUris($uris)
    {
        foreach ($uris as $site => $siteUris) {
            $this->setSiteUris($site, $siteUris);
        }

        return $this;
    }

    public function getItems()
    {
        return $this->load()->items;
    }

    public function getItemsWithoutLoading()
    {
        return $this->items;
    }

    public function isLoaded()
    {
        return $this->loaded;
    }

    public function markAsLoaded()
    {
        $this->loaded = true;

        return $this;
    }

    public function load()
    {
        if ($this->isLoaded()) {
            return $this;
        }

        $cache = Cache::get($this->getItemsCacheKey());

        $this->items = $this->getItemsFromCache(collect($cache));

        $this->markAsLoaded();

        return $this;
    }

    public function getItem($key)
    {
        return $this->load()->items->get($key);
    }

    public function setItem($key, $item)
    {
        $this->items->put($key, $item);

        return $this;
    }

    public function removeItem($key)
    {
        $this->items->forget($key);

        return $this;
    }

    abstract public function key();
    abstract public function getItemsFromCache($cache);

    public function getCacheableMeta()
    {
        return [
            'paths' => $this->paths->toArray(),
            'uris' => $this->uris->toArray()
        ];
    }

    public function getCacheableItems()
    {
        return $this->items->map->toCacheableArray()->all();
    }

    protected function getItemsCacheKey()
    {
        return 'stache::items/' . $this->key();
    }
}
