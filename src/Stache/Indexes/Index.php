<?php

namespace Statamic\Stache\Indexes;

use Statamic\Facades\Stache;
use Statamic\Stache\Exceptions\ItemsNotReadyException;

abstract class Index
{
    protected $store;
    protected $name;
    protected $items = [];
    protected $loaded = false;
    private static array $loadingStack = [];

    public function __construct($store, $name)
    {
        $this->store = $store;
        $this->name = $name;
    }

    public function name()
    {
        return $this->name;
    }

    public function items()
    {
        return collect($this->items);
    }

    public function values()
    {
        return array_values($this->items);
    }

    public function keys()
    {
        return array_keys($this->items);
    }

    public function get($key)
    {
        return $this->items[$key] ?? null;
    }

    public function has($key)
    {
        return array_key_exists($key, $this->items);
    }

    public function put($key, $value)
    {
        $this->items[$key] = $value;
    }

    public function push($value)
    {
        $this->items[] = $value;
    }

    public function load()
    {
        if ($this->loaded) {
            return $this;
        }

        $loadingKey = $this->store->key().'/'.$this->name;

        static::$loadingStack[] = $loadingKey;

        try {
            $this->loaded = true;

            debugbar()->addMessage("Loading index: {$loadingKey}", 'stache');

            $this->items = Stache::cacheStore()->get($this->cacheKey());

            if ($this->items === null) {
                $this->update();
            }

            $this->store->cacheIndexUsage($this);
        } catch (ItemsNotReadyException $e) {
            // Whatever asked will ask again once the items are there, and should get them.
            $this->loaded = false;

            throw $e;
        } finally {
            array_pop(static::$loadingStack);
        }

        return $this;
    }

    public function update()
    {
        if (! Stache::shouldUpdateIndexes()) {
            return $this;
        }

        debugbar()->addMessage("Updating index: {$this->store->key()}/{$this->name}", 'stache');

        // Most indexes are filled in a single pass over the store's items, along with
        // any others that are waiting to be built, rather than each reading every item.
        if ($this->store->fillIndexes($this)) {
            return $this;
        }

        $this->items = $this->getItems();

        $this->cache();

        return $this;
    }

    /**
     * Sets and caches items that have been worked out elsewhere.
     */
    public function fill(array $items)
    {
        $this->items = $items;

        $this->cache();

        return $this;
    }

    /**
     * Runs a callback as if an index were loading, for work that
     * fills indexes without going through load().
     */
    public static function whileLoading(string $key, callable $callback)
    {
        static::$loadingStack[] = $key;

        try {
            return $callback();
        } finally {
            array_pop(static::$loadingStack);
        }
    }

    public function isCached()
    {
        return Stache::cacheStore()->has($this->cacheKey());
    }

    public function cache()
    {
        Stache::cacheStore()->forever($this->cacheKey(), $this->items);
    }

    public function updateItem($item)
    {
        $this->load();

        $this->putItem($item);

        $this->cache();
    }

    private function putItem($item)
    {
        $this->put($this->store->getItemKey($item), $this->getItemValue($item));
    }

    /**
     * Updates a number of items and writes the index once, where updating
     * them one at a time would write the whole of it once for each.
     */
    public function updateItems($items)
    {
        // An index with its own way of updating an item gets to use it.
        if ((new \ReflectionMethod($this, 'updateItem'))->getDeclaringClass()->getName() !== self::class) {
            collect($items)->each(fn ($item) => $this->updateItem($item));

            return;
        }

        if (collect($items)->isEmpty()) {
            return;
        }

        $this->load();

        collect($items)->each(fn ($item) => $this->putItem($item));

        $this->cache();
    }

    public function forgetItem($key)
    {
        $this->load();

        unset($this->items[$key]);

        $this->cache();
    }

    abstract public function getItems();

    public function cacheKey()
    {
        $searches = ['.', '/'];
        $replacements = ['::', '->'];

        if (windows_os()) {
            $replacements[1] = '-]';
            $searches[] = '->';
            $replacements[] = '-]';
        }

        return vsprintf('stache::indexes::%s::%s', [
            $this->store->key(),
            str_replace($searches, $replacements, $this->name),
        ]);
    }

    public function clear()
    {
        $this->loaded = false;
        $this->items = null;

        Stache::cacheStore()->forget($this->cacheKey());
    }

    public function resetMemoizedState()
    {
        $this->loaded = false;
        $this->items = null;
    }

    /** @deprecated */
    public static function currentlyLoading()
    {
        return end(static::$loadingStack) ?: null;
    }

    public static function isLoading(): bool
    {
        return ! empty(static::$loadingStack);
    }
}
