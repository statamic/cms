<?php

namespace Statamic\Stache\Stores;

use Illuminate\Support\Facades\Cache;
use Statamic\Facades\File;
use Symfony\Component\Finder\SplFileInfo;

abstract class BasicStore extends Store
{
    public function getItemFilter(SplFileInfo $file)
    {
        return $file->getExtension() === 'yaml';
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

    protected function getCachedItem($key)
    {
        $cacheKey = $this->getItemCacheKey($key);

        return Cache::get($cacheKey);
    }

    protected function cacheItem($item)
    {
        $key = $this->getItemKey($item);

        $cacheKey = $this->getItemCacheKey($key);

        Cache::forever($cacheKey, $item);
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
        return $this->paths()->get($key);
    }

    protected function getKeyFromPath($path)
    {
        return $this->paths()->flip()->get($path);
    }

    public function save($item)
    {
        $this->writeItemToDisk($item);

        $key = $this->getItemKey($item);

        $this->forgetItem($key);

        $this->setPath($key, $item->path());

        $this->resolveIndexes()->each->updateItem($item);

        $this->cacheItem($item);
    }

    public function delete($item)
    {
        $this->deleteItemFromDisk($item);

        $key = $this->getItemKey($item);

        $this->forgetItem($key);

        $this->forgetPath($key);

        $this->resolveIndexes()->filter->isCached()->each->forgetItem($key);
    }

    protected function writeItemToDisk($item)
    {
        $item->writeFile();
    }

    protected function deleteItemFromDisk($item)
    {
        $item->deleteFile();
    }
}
