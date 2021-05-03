<?php

namespace Statamic\Stache\Stores;

use Illuminate\Support\Facades\Cache;

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

    public function save()
    {
        Cache::forever($this->cacheKey(), $this->keys);
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
        $this->keys[$key] = $path;
    }

    public function isDuplicate($key, $path)
    {
        $existing = $this->keys[$key] ?? null;

        return $existing && $existing !== $path;
    }
}
