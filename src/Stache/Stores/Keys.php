<?php

namespace Statamic\Stache\Stores;

use Illuminate\Support\Facades\Cache;
use Statamic\Stache\Exceptions\DuplicateKeyException;

class Keys
{
    protected $store;
    protected $keys = [];

    public function __construct(Store $store)
    {
        $this->store = $store;
    }

    public function load()
    {
        $this->keys = Cache::get($this->cacheKey(), []);

        return $this;
    }

    public function cache()
    {
        Cache::forever($this->cacheKey(), $this->keys);
    }

    public function clear()
    {
        Cache::forget($this->cacheKey());
    }

    private function cacheKey()
    {
        return 'stache::keys/'.$this->store->key();
    }

    public function all()
    {
        return $this->keys;
    }

    public function setKeys($keys)
    {
        $this->keys = $keys;

        return $this;
    }

    public function add($key, $path)
    {
        $existing = $this->keys[$key] ?? null;

        // If you're adding a key that already exists but has
        // a different path then a duplicate is being added.
        if ($existing && $existing !== $path) {
            throw new DuplicateKeyException($key, $path);
        }

        $this->set($key, $path);
    }

    public function forget($key)
    {
        unset($this->keys[$key]);

        return $this;
    }

    public function set($key, $path)
    {
        $this->keys[$key] = $path;

        return $this;
    }
}
