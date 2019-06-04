<?php

namespace Statamic\Stache\Stores;

use Statamic\Stache\Stache;
use Illuminate\Support\Facades\Cache;
use Illuminate\Filesystem\Filesystem;
use Statamic\Contracts\Data\Localization;
use Statamic\Stache\Exceptions\StoreExpiredException;

abstract class BasicStore extends Store
{
    protected $stache;
    protected $paths;
    protected $uris;
    protected $items;
    protected $loaded = false;
    protected $updated = false;
    protected $expired = false;
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
                $this->setSitePaths($site, []);
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

    public function getSitePath($site, $key)
    {
        return $this->paths->get($site)->get($key);
    }

    public function setSitePath($site, $key, $uri)
    {
        $this->paths->get($site)->put($key, $uri);

        $this->markAsUpdated();

        return $this;
    }

    public function removeSitePath($site, $key)
    {
        $this->paths->get($site)->forget($key);

        $this->markAsUpdated();

        return $this;
    }

    public function getSitePaths($site)
    {
        return $this->paths->get($site);
    }

    public function setSitePaths($site, $paths)
    {
        $this->paths->put($site, collect($paths));

        $this->markAsUpdated();

        return $this;
    }

    public function getPaths()
    {
        return $this->paths;
    }

    public function setPaths($paths)
    {
        foreach ($paths as $site => $sitePaths) {
            $this->setSitePaths($site, $sitePaths);
        }

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
        foreach ($this->paths as $site => $paths) {
            if ($match = $paths->flip()->get($path)) {
                return $match;
            }
        }
    }

    public function getIdMap()
    {
        return $this->getPaths()->mapWithKeys(function ($paths) {
            return $paths;
        })->mapWithKeys(function ($path, $id) {
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

    public function isExpired()
    {
        return $this->expired;
    }

    public function markAsExpired()
    {
        $this->expired = true;

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

        $this->withoutMarkingAsUpdated(function () {
            $cache = Cache::get($this->getItemsCacheKey());

            try {
                $items = $this->getItemsFromCache(collect($cache));
            } catch (StoreExpiredException $e) {
                $this->markAsExpired();
                return;
            }

            $items->each(function ($item, $key) {
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

    public function uncache()
    {
        Cache::forget($this->getItemsCacheKey());

        Cache::forget($this->getMetaCacheKey());
    }

    protected function getItemsCacheKey()
    {
        return 'stache::items/' . $this->key();
    }

    protected function getMetaCacheKey()
    {
        return 'stache::meta/' . $this->key();
    }

    public function cacheHasMeta()
    {
        return Cache::has($this->getMetaCacheKey());
    }

    public function getMetaFromCache()
    {
        $meta = Cache::get($this->getMetaCacheKey(), $this->getCacheableMeta());

        return [$this->key() => $meta];
    }

    public function loadMeta($data)
    {
        $this->withoutMarkingAsUpdated(function () use ($data) {
            $this
                ->setPaths($data['paths'])
                ->setUris($data['uris']);
        });
    }

    public function insert($item, $key = null)
    {
        $key = $key ?? $item->id();

        $this->setItem($key, $item);

        $site = $item instanceof Localization
            ? $item->locale()
            : $this->stache->sites()->first();

        $this->setSitePath($site, $key, $item->path());

        if ($this->shouldStoreUri($item)) {
            $this->setSiteUri($site, $key, $item->uri());
        }

        $this->markAsUpdated();

        return $this;
    }

    public function shouldStoreUri($item)
    {
        return method_exists($item, 'uri');
    }

    public function removeByPath($path)
    {
        $id = $this->getIdFromPath($path);

        $item = $this->getItem($id);

        $this->removeItem($id);

        $site = $item instanceof Localization
            ? $item->locale()
            : $this->stache->sites()->first();

        $this->removeSiteUri($site, $id);
        $this->removeSitePath($site, $id);

        return $this;
    }

    public function remove($item)
    {
        $key = is_object($item) ? $item->id() : $item;

        $this->removeItem($key);

        // TODO, if localizable, it should loop over the locales and remove each respective path.
        // If not localizable, remove the default site like we're doing now.
        $this->removeSitePath($this->stache->sites()->first(), $key);

        $this->forEachSite(function ($site, $store) use ($item, $key) {
            $store->removeSiteUri($site, $key);
        });

        return $this;
    }
}
