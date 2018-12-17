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
    protected $updated = false;
    protected $markUpdates = true;
    protected $files;

    public function __construct(Stache $stache, Filesystem $files)
    {
        $this->stache = $stache;
        $this->files = $files;

        $this->paths = collect();
        $this->uris = collect();
        $this->items = collect();

        $this->withoutMarkingAsUpdated(function () {
            $this->forEachSite(function ($site) {
                $this->setSiteUris($site, []);
            });
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

        $this->markAsUpdated();

        return $this;
    }

    public function getPath($key)
    {
        return $this->paths->get($key);
    }

    public function setPath($key, $path)
    {
        $this->paths->put($key, $path);

        $this->markAsUpdated();

        return $this;
    }

    public function removePath($key)
    {
        $this->paths->forget($key);

        $this->markAsUpdated();

        return $this;
    }

    public function getSiteUri($site, $key)
    {
        return $this->uris->get($site)->get($key);
    }

    public function setSiteUri($site, $key, $uri)
    {
        $this->uris->get($site)->put($key, $uri);

        $this->markAsUpdated();

        return $this;
    }

    public function removeSiteUri($site, $key)
    {
        $this->uris->get($site)->forget($key);

        $this->markAsUpdated();

        return $this;
    }

    public function getSiteUris($site)
    {
        return $this->uris->get($site);
    }

    public function setSiteUris($site, $uris)
    {
        $this->uris->put($site, collect($uris));

        $this->markAsUpdated();

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

    public function getIdFromPath($path)
    {
        return $this->getPaths()->filter()->flip()->get($path);
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

    public function isUpdated()
    {
        return $this->updated;
    }

    public function markAsUpdated()
    {
        if ($this->markUpdates) {
            $this->updated = true;
        }

        return $this;
    }

    public function withoutMarkingAsUpdated($callback)
    {
        $this->markUpdates = false;

        $return = $callback();

        $this->markUpdates = true;

        return $return;
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

        $this->withoutMarkingAsUpdated(function () {
            $cache = Cache::get($this->getItemsCacheKey());

            $this->getItemsFromCache(collect($cache))->each(function ($item, $key) {
                $this->setItem($key, $item);
            });
        });

        $this->markAsLoaded();

        debugbar()->addMessage("Loaded [{$this->key()}] store", 'stache');

        return $this;
    }

    public function getItem($key)
    {
        return $this->load()->items->get($key);
    }

    public function setItem($key, $item)
    {
        $this->items->put($key, $item);

        $this->markAsUpdated();

        return $this;
    }

    public function removeItem($key)
    {
        $this->items->forget($key);

        $this->markAsUpdated();

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
        $this->withoutMarkingAsUpdated(function () use ($data) {
            $this
                ->setPaths($data['paths'])
                ->setUris($data['uris']);
        });
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

        $this->markAsUpdated();

        return $this;
    }

    public function remove($item)
    {
        $key = is_object($item) ? $item->id() : $item;

        $this
            ->removeItem($key)
            ->removePath($key);

        $this->forEachSite(function ($site, $store) use ($item, $key) {
            $store->removeSiteUri($site, $key);
        });

        return $this;
    }
}
