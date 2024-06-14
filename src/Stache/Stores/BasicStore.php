<?php

namespace Statamic\Stache\Stores;

use Statamic\Facades\File;
use Statamic\Facades\Stache;
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
            if (method_exists($item, 'syncOriginal')) {
                $item->syncOriginal();
            }

            return $item;
        }

        $item = $this->makeItemFromFile($path, File::get($path));

        $this->cacheItem($item);

        if (method_exists($item, 'syncOriginal')) {
            $item->syncOriginal();
        }

        return $item;
    }

    public function getItemValues($keys, $valueIndex, $keyIndex)
    {
        // This is for performance. There's no need to resolve anything
        // else if we're looking for the keys. We have them already.
        if ($valueIndex === 'id' && ! $keyIndex) {
            return $keys;
        }

        $values = $this->getIndexedValues($valueIndex, $keys);

        if (! $keyIndex) {
            return $values->values();
        }

        $keyValues = $this->getIndexedValues($keyIndex, $keys);

        return $keys->mapWithKeys(fn ($key) => [$keyValues[$key] => $values[$key]]);
    }

    private function getIndexedValues($name, $only)
    {
        // We don't want *all* the values in the index. We only want the requested keys. They are
        // provided as an array of IDs. It's faster to do has() than contains() so we'll flip them.
        $only = $only->flip();

        return $this->resolveIndex($name)
            ->load()
            ->items()
            ->filter(fn ($value, $key) => $only->has($key));
    }

    protected function getCachedItem($key)
    {
        $cacheKey = $this->getItemCacheKey($key);

        return Stache::cacheStore()->get($cacheKey);
    }

    protected function cacheItem($item)
    {
        $key = $this->getItemKey($item);

        $cacheKey = $this->getItemCacheKey($key);

        Stache::cacheStore()->forever($cacheKey, $item);
    }

    public function forgetItem($key)
    {
        Stache::cacheStore()->forget($this->getItemCacheKey($key));
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
