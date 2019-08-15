<?php

namespace Statamic\Stache\Stores;

use Statamic\API\File;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\Finder\SplFileInfo;

abstract class BasicStore extends Store
{
    abstract public function filter(SplFileInfo $path);
    abstract public function makeItemFromFile($path, $contents);

    public function getItem($key)
    {
        $path = $this->getPath($key);

        $cacheKey = $this->getItemCacheKey($key);

        if ($item = Cache::get($cacheKey)) {
            return $item;
        }

        return Cache::rememberForever($cacheKey, function () use ($path) {
            return $this->makeItemFromFile($path, File::get($path));
        });
    }

    public function forgetItem($key)
    {
        Cache::forget($this->getItemCacheKey($key));
    }

    protected function getItemCacheKey($key)
    {
        return "stache::items::{$this->key()}::{$key}";
    }

    protected function getPath($key)
    {
        return $this->index('path')->get($key);
    }
}