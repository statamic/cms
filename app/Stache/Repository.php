<?php

namespace Statamic\Stache;

use Illuminate\Support\Collection;
use Statamic\Events\Stache\RepositoryItemInserted;
use Statamic\Events\Stache\RepositoryItemRemoved;
use Statamic\Events\StacheItemInserted;

class Repository
{
    /**
     * Identifying key
     *
     * @var string
     */
    protected $key;

    /**
     * Key in the cache where the items are located
     *
     * @var string
     */
    protected $cache_key;

    /**
     * ID to path mappings, grouped by locale
     *
     * @var Collection
     */
    protected $paths;

    /**
     * ID to URI mappings, grouped by locale
     *
     * @var Collection
     */
    protected $uris;

    /**
     * ID to data object mappings
     *
     * @var Collection
     */
    protected $items;

    /**
     * Whether the data objects have been loaded
     *
     * @var bool
     */
    public $loaded = false;

    public function __construct($key = null, $cache_key = null)
    {
        $this->key = $key;
        $this->cache_key = $cache_key;
        $this->uris = collect();
        $this->paths = collect();
        $this->items = collect();
    }

    public function key()
    {
        return $this->key;
    }

    public function cacheKey()
    {
        if (! $this->cache_key) {
            return $this->key();
        }

        return $this->cache_key;
    }

    public function getUris($locale = null)
    {
        $locale = $locale ?: default_locale();

        $this->ensureUrisForLocale($locale);

        return $this->uris->get($locale, collect());
    }

    public function getUrisForAllLocales()
    {
        return $this->uris;
    }

    public function setUris($uris, $locale = null)
    {
        $locale = $locale ?: default_locale();

        $this->uris->put($locale, collect($uris));

        return $this;
    }

    public function setUrisForAllLocales($uris)
    {
        foreach ($uris as $locale => $localized_uris) {
            $this->uris->put($locale, collect($localized_uris));
        }

        return $this;
    }

    public function getUri($id, $locale = null)
    {
        $locale = $locale ?: default_locale();

        $this->ensureUrisForLocale($locale);

        return $this->uris->get($locale)->get($id);
    }

    public function setUri($id, $url, $locale = null)
    {
        $locale = $locale ?: default_locale();

        $this->ensureUrisForLocale($locale);

        $this->uris->get($locale)->put($id, $url);

        return $this;
    }

    public function removeUri($id, $locale = null)
    {
        $locale = $locale ?: default_locale();

        $this->ensureUrisForLocale($locale);

        $this->uris->get($locale)->forget($id);

        return $this;
    }

    private function ensureUrisForLocale($locale)
    {
        if (! $this->uris->has($locale)) {
            $this->uris->put($locale, collect());
        }
    }

    public function getPaths($locale = null)
    {
        $locale = $locale ?: default_locale();

        $this->ensurePathsForLocale($locale);

        return $this->paths->get($locale);
    }

    public function getPathsForAllLocales()
    {
        return $this->paths;
    }

    public function setPaths($paths, $locale = null)
    {
        $locale = $locale ?: default_locale();

        $this->paths->put($locale, collect($paths));

        return $this;
    }

    public function setPathsForAllLocales($paths)
    {
        foreach ($paths as $locale => $localized_paths) {
            $this->paths->put($locale, collect($localized_paths));
        }

        return $this;
    }

    public function getPath($id, $locale = null)
    {
        $locale = $locale ?: default_locale();

        $this->ensurePathsForLocale($locale);

        return $this->paths->get($locale)->get($id);
    }

    public function setPath($id, $path, $locale = null)
    {
        $locale = $locale ?: default_locale();

        $this->ensurePathsForLocale($locale);

        $this->paths->get($locale)->put($id, $path);

        return $this;
    }

    public function removePath($id, $locale = null)
    {
        $locale = $locale ?: default_locale();

        $this->ensurePathsForLocale($locale);

        $this->paths->get($locale)->forget($id);

        return $this;
    }

    private function ensurePathsForLocale($locale)
    {
        if (! $this->paths->has($locale)) {
            $this->paths->put($locale, collect());
        }
    }

    public function getItems()
    {
        if ($this->loaded) {
            return $this->items;
        }

        $this->load();

        return $this->items;
    }

    public function load()
    {
        if ($this->loaded) {
            return $this;
        }

        $this->items = app(ItemLoader::class)->load($this);

        $this->loaded = true;

        return $this;
    }

    public function setItems($items)
    {
        $this->items = collect($items);

        return $this;
    }

    public function getItem($id)
    {
        $this->load();

        return $this->items->get($id);
    }

    public function setItem($id, $item)
    {
        $this->items->put($id, $item);

        event(new RepositoryItemInserted($this, $id, $item));

        return $this;
    }

    public function removeItem($id)
    {
        $this->load();

        $item = $this->items->pull($id);

        $this->paths->get(default_locale())->forget($id);

        if ($uris = $this->uris->get(default_locale())) {
            $uris->forget($id);
        }

        event(new RepositoryItemRemoved($this, $id, $item));

        return $this;
    }

    public function clear()
    {
        $this->uris = collect();
        $this->paths = collect();
        $this->items = collect();

        $this->loaded = true;
    }

    public function getIds($locale = null)
    {
        $locale = $locale ?: default_locale();

        $this->ensurePathsForLocale($locale);

        $ids = $this->paths->get($locale)->keys()->all();

        // Make a collection where the keys and values are both
        // IDs. This lets us get the IDs as values like we'd
        // expect, but also do `->has($id)` which is faster.
        return collect(array_combine($ids, $ids));
    }

    public function getIdByPath($path, $locale = null)
    {
        $locale = $locale ?: default_locale();

        $this->ensurePathsForLocale($locale);

        return $this->paths->get($locale)->flip()->get($path);
    }

    public function getIdByUri($url, $locale = null)
    {
        $locale = $locale ?: default_locale();

        $this->ensureUrisForLocale($locale);

        return $this->uris->get($locale)->filter()->flip()->get($url);
    }
}
