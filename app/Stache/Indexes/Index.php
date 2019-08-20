<?php

namespace Statamic\Stache\Indexes;

use Illuminate\Support\Facades\Cache;

abstract class Index
{
    protected $store;
    protected $name;
    protected $items = [];

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

    public function load()
    {
        debugbar()->addMessage("Loading index: {$this->store->key()}/{$this->name}", 'stache');

        $this->items = Cache::get($this->cacheKey());

        if (! $this->items) {
            $this->update();
        }

        $this->store->cacheIndexUsage($this);

        return $this;
    }

    public function update()
    {
        debugbar()->addMessage("Updating index: {$this->store->key()}/{$this->name}", 'stache');

        $this->items = $this->getItems();

        $this->cache();

        return $this;
    }

    protected function cache()
    {
        Cache::forever($this->cacheKey(), $this->items);
    }

    public function updateItem($item)
    {
        $this->load();

        $this->items[$this->store->getItemKey($item)] = $this->getItemValue($item);

        $this->cache();
    }

    abstract public function getItems();

    public function cacheKey()
    {
        return vsprintf('stache::indexes::%s::%s', [
            $this->store->key(),
            str_replace('.', '::', $this->name)
        ]);
    }
}