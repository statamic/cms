<?php

namespace Statamic\Stache\Stores;

use Statamic\API\File;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\Finder\SplFileInfo;

abstract class BasicStore extends Store
{
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
        if (! $path = $this->getPath($key)) {
            return null;
        }

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

    public function save($item)
    {
        $item->writeFile();

        $this->forgetItem($this->getItemKey($item));

        $this->updateItemIndexes($item);
    }
}