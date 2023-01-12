<?php

namespace Statamic\Stache\Stores;

use Facades\Statamic\Stache\Traverser;
use Illuminate\Support\Facades\Cache;
use Statamic\Facades\File;
use Statamic\Facades\Path;
use Statamic\Facades\Stache;
use Statamic\Stache\Exceptions\DuplicateKeyException;
use Statamic\Stache\Indexes;
use Statamic\Stache\Indexes\Index;
use Statamic\Statamic;
use Statamic\Support\Arr;

abstract class Store
{
    protected $directory;
    protected $valueIndex = Indexes\Value::class;
    protected $customIndexes = [];
    protected $defaultIndexes = ['id', 'path'];
    protected $storeIndexes = [];
    protected $usedIndexes;
    protected $fileChangesHandled = false;
    protected $paths;
    protected $fileItems;
    protected $shouldCacheFileItems = false;
    protected $modified;
    protected $keys;

    public function directory($directory = null)
    {
        if (func_num_args() === 0) {
            return $this->directory;
        }

        $this->directory = str_finish(Path::tidy($directory), '/');

        return $this;
    }

    public function index($name)
    {
        $this->handleFileChanges();

        return $this->resolveIndex($name)->load();
    }

    protected function resolveIndex($name)
    {
        $cached = app('stache.indexes');

        if ($cached->has($key = "{$this->key()}.{$name}")) {
            return $cached->get($key);
        }

        $class = $this->indexes()->get($name, $this->valueIndex);

        $index = new $class($this, $name);

        $cached->put($key, $index);

        return $index;
    }

    public function getItemsFromFiles()
    {
        if ($this->shouldCacheFileItems && $this->fileItems) {
            return $this->fileItems;
        }

        return $this->fileItems = $this->paths()->map(function ($path, $key) {
            return $this->getItem($key);
        });
    }

    public function getItemKey($item)
    {
        return $item->id();
    }

    public function getItems($keys)
    {
        $this->handleFileChanges();

        return collect($keys)->map(function ($key) {
            return $this->getItem($key);
        });
    }

    abstract public function getItem($key);

    public function indexUsage()
    {
        $key = $this->indexUsageCacheKey();

        return $this->usedIndexes = $this->usedIndexes ?? collect(Cache::get($key, []));
    }

    public function cacheIndexUsage($index)
    {
        $index = $index instanceof Index ? $index->name() : $index;

        $indexes = $this->indexUsage();

        if ($indexes->contains($index)) {
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

    public function indexes($withUsages = true)
    {
        $storeIndexConfigKey = $this instanceof ChildStore ? $this->parent->key() : $this->key();

        $indexes = collect(array_merge(
            $this->defaultIndexes,
            $this->storeIndexes(),
            config('statamic.stache.indexes', []),
            config("statamic.stache.stores.{$storeIndexConfigKey}.indexes", [])
        ));

        if ($withUsages) {
            $indexes = $indexes->merge($this->indexUsage()->all());
        }

        return $indexes->unique(function ($value, $key) {
            return is_int($key) ? $value : $key;
        })->mapWithKeys(function ($index, $key) {
            return is_int($key)
                ? [$index => $this->valueIndex]
                : [$key => $index];
        });
    }

    public function resolveIndexes($withUsages = true)
    {
        return $this->indexes($withUsages)->map(function ($index, $name) {
            return $this->resolveIndex($name);
        });
    }

    protected function storeIndexes()
    {
        return $this->storeIndexes;
    }

    public function handleFileChanges()
    {
        $this->modified = collect();

        // We only want to act on any file changes one time per store.
        if ($this->fileChangesHandled) {
            return;
        }

        $this->fileChangesHandled = true;

        // This whole process can be disabled to save overhead, at the expense of needing to update
        // the cache manually. If the Control Panel is being used, or the cache is cleared when
        // deployed, for example, this will happen naturally and disabling is a good idea.
        if (! config('statamic.stache.watcher')) {
            return;
        }

        // Get the existing files and timestamps from the cache.
        $cacheKey = "stache::timestamps::{$this->key()}";
        $existing = collect(Cache::get($cacheKey, []));

        // Get the files and timestamps from the filesystem right now.
        $files = Traverser::filter([$this, 'getItemFilter'])->traverse($this);

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

        // Flush cached instances of deleted items.
        $deleted->each(function ($path) {
            collect($this->getKeyFromPath($path))->each(function ($key) use ($path) {
                $this->forgetItem($key);
                $this->forgetPath($key);
                $this->resolveIndexes()->filter->isCached()->each->forgetItem($key);
                $this->handleDeletedItem($path, $key);
            });
        });

        // Get items from every file that was modified.
        $modified = $modified->flatMap(function ($timestamp, $path) {
            return Arr::wrap($this->getItemFromModifiedPath($path));
        });

        // Remove items with duplicate IDs/keys
        $modified = $modified->reject(function ($item) {
            try {
                $this->keys()->add($this->getItemKey($item), $item->path());
            } catch (DuplicateKeyException $e) {
                $isDuplicate = true;
                Stache::duplicates()->track($this, $e->getKey(), $e->getPath());
            }

            return $isDuplicate ?? false;
        });

        // Put the items into the cache
        $modified->each(function ($item) {
            $this->cacheItem($item);
        });

        // There may be duplicate items when we're dealing with items that are split across files.
        // For example, a global set can have the base file, plus a file for each localization.
        // They'd all resolve to the same item though, so just reduce them down to the same.
        $modified = $modified->unique(function ($item) {
            return $this->getItemKey($item);
        });

        $modified->each(function ($item) {
            $this->handleModifiedItem($item);
        });

        // Update modified items in every index.
        $this->resolveIndexes()->filter->isCached()->each(function ($index) use ($modified) {
            $modified->each(function ($item) use ($index) {
                $index->updateItem($item);
            });
        });

        $this->modified = $modified;
    }

    protected function handleModifiedItem($item)
    {
        //
    }

    protected function handleDeletedItem($item, $key)
    {
        //
    }

    protected function getItemFromModifiedPath($path)
    {
        return $this->makeItemFromFile($path, File::get($path));
    }

    public function paths()
    {
        $this->handleFileChanges();

        if ($this->paths && ! Statamic::isWorker()) {
            return $this->paths;
        }

        if ($paths = Cache::get($this->pathsCacheKey())) {
            return $this->paths = collect($paths);
        }

        $files = Traverser::filter([$this, 'getItemFilter'])->traverse($this);

        $fileItems = $files->map(function ($timestamp, $path) {
            return [
                'item' => $item = $this->makeItemFromFile($path, File::get($path)),
                'key' => $this->getItemKey($item),
                'path' => $path,
            ];
        });

        $items = $fileItems->reject(function ($item) {
            try {
                $this->keys()->add($item['key'], $item['path']);
            } catch (DuplicateKeyException $e) {
                $isDuplicate = true;
                Stache::duplicates()->track($this, $e->getKey(), $e->getPath());
            }

            return $isDuplicate ?? false;
        });

        $paths = $items->pluck('path', 'key');

        $this->cachePaths($paths);

        $this->keys()->cache();

        Stache::duplicates()->cache();

        return $paths;
    }

    protected function forgetPath($key)
    {
        $paths = $this->paths();

        unset($paths[$key]);

        $this->cachePaths($paths);

        $this->keys()->forget($key)->cache();
    }

    protected function setPath($key, $path)
    {
        $paths = $this->paths();

        $paths[$key] = $path;

        $this->cachePaths($paths);

        $this->keys()->set($key, $path)->cache();
    }

    protected function cachePaths($paths)
    {
        Cache::forever($this->pathsCacheKey(), $paths->all());

        $this->paths = $paths;

        $this->cacheIndexUsage('path');
    }

    public function clearCachedPaths()
    {
        $this->paths = null;
        Cache::forget($this->pathsCacheKey());
    }

    protected function pathsCacheKey()
    {
        return "stache::indexes::{$this->key()}::path";
    }

    public function clear()
    {
        $this->paths()->keys()->each(function ($key) {
            $this->forgetItem($key);
        });

        $this->resolveIndexes()->each(function ($index) {
            $index->clear();
            app('stache.indexes')->forget("{$this->key()}.{$index->name()}");
        });

        $this->usedIndexes = collect();
        Cache::forget($this->indexUsageCacheKey());

        $this->clearCachedPaths();

        $this->keys()->clear();

        Cache::forget("stache::timestamps::{$this->key()}");
    }

    public function warm()
    {
        $this->shouldCacheFileItems = true;

        $this->resolveIndexes()->each->update();

        $this->shouldCacheFileItems = false;
        $this->fileItems = null;
    }

    public function keys()
    {
        if ($this->keys) {
            return $this->keys;
        }

        return $this->keys = (new Keys($this))->load();
    }
}
