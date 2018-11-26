<?php

namespace Statamic\Stache\Stores;

use Statamic\Stache\Stache;
use Illuminate\Support\Facades\Cache;
use Illuminate\Filesystem\Filesystem;

abstract class BasicStore extends Store
{
    protected $stache;
    protected $paths;
    protected $uris;
    protected $items;
    protected $loaded = false;
    protected $files;

    public function __construct(Stache $stache, Filesystem $files)
    {
        $this->stache = $stache;
        $this->files = $files;

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

    public function getIdFromUri($uri, $site = null)
    {
        $site = $site ?? $this->stache->sites()->first();

        return $this->getSiteUris($site)->filter()->flip()->get($uri);
    }

    public function getIdMap()
    {
        return $this->paths->keys()->mapWithKeys(function ($id) {
            return [$id => $this->key()];
        });
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

        $this->loadingComplete();

        return $this;
    }

    protected function loadingComplete()
    {
        //
    }

    public function load()
    {
        if ($this->isLoaded()) {
            return $this;
        }

        $cache = Cache::get($this->getItemsCacheKey());

        $this->getItemsFromCache(collect($cache))->each(function ($item, $key) {
            $this->setItem($key, $item);
        });

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

    public function getCacheableMeta()
    {
        return [
            'paths' => $this->paths->toArray(),
            'uris' => $this->uris->toArray()
        ];
    }

    public function getCacheableItems()
    {
        return $this->items->map(function ($item) {
            return method_exists($item, 'toCacheableArray') ? $item->toCacheableArray() : $item;
        })->all();
    }

    public function cache()
    {
        Cache::forever($this->getItemsCacheKey(), $this->getCacheableItems());

        Cache::forever($this->getMetaCacheKey(), $this->getCacheableMeta());
    }

    protected function getItemsCacheKey()
    {
        return 'stache::items/' . $this->key();
    }

    protected function getMetaCacheKey()
    {
        return 'stache::meta/' . $this->key();
    }

    public function getMetaFromCache()
    {
        if ($meta = Cache::get($this->getMetaCacheKey())) {
            return [$this->key() => $meta];
        }
    }

    public function loadMeta($data)
    {
        $this
            ->setPaths($data['paths'])
            ->setUris($data['uris']);
    }

    public function insert($item, $key = null, $path = null)
    {
        $key = $key ?? $item->id();

        $this
            ->setItem($key, $item)
            ->setPath($key, $path ?? $item->path());

        if (method_exists($item, 'uri')) {
            $this->forEachSite(function ($site, $store) use ($item, $key) {
                $store->setSiteUri($site, $key, $item->uri());
            });
        }

        return $this;
    }
}
