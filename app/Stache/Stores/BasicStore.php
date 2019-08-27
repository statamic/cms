<?php

namespace Statamic\Stache\Stores;

use Statamic\API\File;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\Finder\SplFileInfo;

abstract class BasicStore extends Store
{
    protected $items = [];

    public function getFileFilter(SplFileInfo $file)
    {
        return $file->getExtension() === 'yaml';
    }

    public function getItemFilter(SplFileInfo $file)
    {
        return $this->getFileFilter($file);
    }

    abstract public function makeItemFromFile($path, $contents);

    public function getItem($key)
    {
        $this->handleFileChanges();

        if (! $path = $this->getPath($key)) {
            return null;
        }

        if ($item = $this->getCachedItem($key)) {
            return $item;
        }

        $item = $this->makeItemFromFile($path, File::get($path));

        $this->cacheItem($item);

        return $item;
    }

    public function getItemByPath($path)
    {
        return $this->getItem($this->getKeyFromPath($path));
    }

    protected function getCachedItem($key)
    {
        $cacheKey = $this->getItemCacheKey($key);

        if ($cached = $this->items[$key] ?? null) {
            return $cached;
        }

        return $this->items[$key] = Cache::get($cacheKey);
    }

    protected function cacheItem($item)
    {
        $key = $this->getItemKey($item);

        $cacheKey = $this->getItemCacheKey($key);

        $this->items[$key] = $item;

        Cache::forever($cacheKey, $item);
    }

    public function forgetItem($key)
    {
        unset($this->items[$key]);

        Cache::forget($this->getItemCacheKey($key));
    }

    protected function getItemCacheKey($key)
    {
        return "stache::items::{$this->key()}::{$key}";
    }

    protected function getPath($key)
    {
        return $this->paths()->get($key);
    }

    protected function getKeyFromPath($path)
    {
        return $this->paths()->flip()->get($path);
    }

    public function save($item)
    {
        $item->writeFile();

        $key = $this->getItemKey($item);

        $this->forgetItem($key);

        $this->setPath($key, $item->path());

        $this->updateItemIndexes($item);

        $this->cacheItem($item);
    }
}
