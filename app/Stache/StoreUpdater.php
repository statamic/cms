<?php

namespace Statamic\Stache;

use Illuminate\Support\Facades\Cache;
use Illuminate\Filesystem\Filesystem;
use Facades\Statamic\Stache\Traverser;

class StoreUpdater
{
    protected $store;
    protected $filesystem;
    protected $files;
    protected $timestamps;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function store($store)
    {
        $this->store = $store;

        return $this;
    }

    public function files()
    {
        return $this->files = $this->files ?? Traverser::traverse($this->store);
    }

    public function timestamps()
    {
        return $this->timestamps = $this->timestamps
            ?? collect(Cache::get($this->timestampsCacheKey(), []));
    }

    protected function timestampsCacheKey()
    {
        return 'stache::timestamps/' . $this->store->key();
    }

    public function modifiedFiles()
    {
        return $this->files()->filter(function ($timestamp, $path) {
            // No existing timestamp, it must be a new file.
            if (! $existingTimestamp = $this->timestamps()->get($path)) {
                return true;
            }

            // If the existing timestamp is less/older, it's modified.
            return $existingTimestamp < $timestamp;
        })->keys();
    }

    public function deletedFiles()
    {
        return $this->timestamps()->keys()
            ->diff($this->files()->keys())
            ->values();
    }

    public function update()
    {
        foreach ($this->modifiedFiles() as $path) {
            $item = $this->store->createItemFromFile($path, $this->filesystem->get($path));
            $key = $this->store->getItemKey($item, $path);

            $this->store
                ->setItem($key, $item)
                ->setPath($key, $path)
                ->forEachSite(function ($site, $store) use ($item, $key) {
                    $store->setSiteUri($site, $key, $item->uri());
                });
        }

        foreach ($this->deletedFiles() as $path) {
            $id = $this->store->getPaths()->flip()->get($path);
            $this->store->removeItem($id);
        }

        $this->store->markAsLoaded();

        $this->cache();
    }

    protected function cache()
    {
        if ($this->modifiedFiles()->isEmpty() && $this->deletedFiles()->isEmpty()) {
            return;
        }

        Cache::forever($this->timestampsCacheKey(), $this->files()->all());

        $this->store->cache();
    }
}
