<?php

namespace Statamic\Stache\Stores;

use Statamic\API\File;
use Statamic\Stache\Indexes;
use Illuminate\Support\Facades\Cache;
use Facades\Statamic\Stache\Traverser;

abstract class Store
{
    protected $directory;
    protected $customIndexes = [];
    protected $defaultIndexes = [
        'id',
        'path',
    ];
    protected $storeIndexes = [];
    protected $usedIndexes;
    protected static $indexes = [];
    protected $fileChangesHandled = false;
    protected $paths;

    public function directory($directory = null)
    {
        if ($directory === null) {
            return $this->directory;
        }

        $this->directory = str_finish($directory, '/');

        return $this;
    }

    public function index($name)
    {
        $this->handleFileChanges();

        return $this->resolveIndex($name)->load();
    }

    protected function resolveIndex($name)
    {
        if (isset(static::$indexes[$this->key()][$name])) {
            return static::$indexes[$this->key()][$name];
        }

        $class = $this->indexes()->get($name);

        $index = new $class($this, $name);

        static::$indexes[$this->key()][$name] = $index;

        return $index;
    }

    public function getItemsFromFiles()
    {
        $files = Traverser::filter([$this, 'getItemFilter'])->traverse($this);

        return $files->map(function ($timestamp, $path) {
            return $this->getItemByPath($path);
        })->keyBy(function ($item) {
            return $this->getItemKey($item);
        });
    }

    public function getItemKey($item)
    {
        return $item->id();
    }

    public function getItems($keys)
    {
        return collect($keys)->map(function ($key) {
            return $this->getItem($key);
        });
    }

    abstract public function getItem($key);

    public function updateItemIndexes($item)
    {
        $this->resolveIndexes()->each->updateItem($item);
    }

    public function indexUsage()
    {
        $key = $this->indexUsageCacheKey();

        return $this->usedIndexes = $this->usedIndexes ?? collect(Cache::get($key, []));
    }

    public function cacheIndexUsage($index)
    {
        $indexes = $this->indexUsage();

        if ($indexes->contains($index = $index->name())) {
            $this->usedIndexes = $indexes;
            return;
        }

        $indexes->push($index);

        $this->usedIndexes = $indexes;

        Cache::put($this->indexUsageCacheKey(), $indexes->all());
    }

    protected function indexUsageCacheKey()
    {
        return "stache::indexes::{$this->key()}::_indexes";
    }

    public function indexes()
    {
        return collect(array_merge(
            $this->defaultIndexes,
            $this->storeIndexes,
            config('statamic.stache.indexes', []),
            config("statamic.stache.stores.{$this->key()}.indexes", []),
            $this->indexUsage()->all()
        ))->unique(function ($value, $key) {
            return is_int($key) ? $value : $key;
        })->mapWithKeys(function ($index, $key) {
            return is_int($key)
                ? [$index => Indexes\Value::class]
                : [$key => $index];
        });
    }

    public function resolveIndexes()
    {
        return $this->indexes()->map(function ($index, $name) {
            return $this->resolveIndex($name);
        });
    }

    public function handleFileChanges()
    {
        // We only want to act on any file changes one time per store.
        if ($this->fileChangesHandled) {
            return;
        }

        $this->fileChangesHandled = true;

        // This whole process can be disabled to save overhead, at the expense of needing to update
        // the cache manually. If the Control Panel is being used, or the cache is cleared when
        // deployed, for example, this will happen naturally and disabling is a good idea.
        if (! config('statamic.stache.update_every_request')) {
            return;
        }

        // Get the existing files and timestamps from the cache.
        $cacheKey = "stache::timestamps::{$this->key()}";
        $existing = collect(Cache::get($cacheKey, []));

        // Get the files and timestamps from the filesystem right now.
        $files = Traverser::filter([$this, 'getFileFilter'])->traverse($this);

        // Cache the files and timestamps, ready for comparisons on the next request.
        // We'll do it now since there are multiple early returns coming up.
        Cache::forever($cacheKey, $files->all());

        // If there are no existing file timestamps in the cache, there's nothing to update.
        if ($existing->isEmpty()) {
            return;
        }

        // Get all the modified files.
        // This includes both newly added files, and existing files that have been changed.
        $modified = $files->filter(function ($timestamp, $path) use ($existing) {
            // No existing timestamp, it must be a new file.
            if (! $existingTimestamp = $existing->get($path)) {
                return true;
            }

            // If the existing timestamp is less/older, it's modified.
            return $existingTimestamp < $timestamp;
        });

        // Get all the deleted files.
        // This would be any paths that exist in the cached array that aren't there anymore.
        $deleted = $existing->keys()->diff($files->keys())->values();

        // If there are no modified or deleted files, there's nothing to update.
        if ($modified->isEmpty() && $deleted->isEmpty()) {
            return;
        }

        // Get a path to key mapping, so we can easily get the keys of existing files.
        $pathMap = $this->paths()->items()->flip();

        // Flush cached instances of deleted items.
        $deleted->each(function ($path) use ($pathMap) {
            $this->forgetItem($pathMap[$path]);
        });

        // Flush the cached instances of modified items.
        $modified->each(function ($timestamp, $path) use ($pathMap) {
            if ($key = $pathMap->get($path)) {
                $this->forgetItem($key);
            }
        });

        // Load all the indexes so we're dealing with fresh items in both loops.
        $indexes = $this->resolveIndexes()->each->load();

        // Remove deleted items from every index.
        $indexes->each(function ($index) use ($deleted, $pathMap) {
            $deleted->each(function ($path) use ($index, $pathMap) {
                $index->forgetItem($pathMap->get($path));
            });
        });

        // Update modified items in every index.
        $indexes->each(function ($index) use ($modified, $pathMap) {
            $modified->each(function ($timestamp, $path) use ($index, $pathMap) {
                $key = $pathMap[$path] ?? null;
                $item = $key
                    ? $this->getItem($pathMap[$path])
                    : $this->makeItemFromFile($path, File::get($path));
                $index->updateItem($item);
            });
        });
    }

    public function paths()
    {
        $key = "stache::indexes::{$this->key()}::_paths";

        if ($this->paths) {
            return $this->paths;
        }

        if ($paths = Cache::get($key)) {
            return $this->paths = collect($paths);
        }

        $files = Traverser::filter([$this, 'getItemFilter'])->traverse($this);

        $paths = $files->mapWithKeys(function ($timestamp, $path) {
            $item = $this->makeItemFromFile($path, File::get($path));
            $this->cacheItem($item);
            return [$this->getItemKey($item) => $path];
        });

        Cache::forever($key, $paths->all());

        $this->paths = $paths;

        return $paths;
    }
}
