<?php

namespace Statamic\Stache\Stores;

use Facades\Statamic\Stache\Traverser;
use ReflectionMethod;
use Statamic\Facades\File;
use Statamic\Facades\Path;
use Statamic\Facades\Stache;
use Statamic\Stache\Exceptions\DuplicateKeyException;
use Statamic\Stache\Exceptions\ItemsNotReadyException;
use Statamic\Stache\Indexes;
use Statamic\Stache\Indexes\Index;
use Statamic\Support\Arr;
use Statamic\Support\Str;

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
    private ?array $filling = null;
    private bool $makingItems = false;
    private ?string $filledDuringWarm = null;
    private static array $buildsItsOwnItems = [];
    private static ?string $warmRun = null;

    /**
     * @return string
     */
    abstract public function key();

    /**
     * @param  string|null  $directory
     * @return ($directory is null ? string : static)
     */
    public function directory($directory = null)
    {
        if (func_num_args() === 0) {
            return $this->directory;
        }

        $this->directory = Str::finish(Path::tidy($directory), '/');

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

    abstract public function getItemValues($keys, $valueIndex, $keyIndex);

    public function indexUsage()
    {
        $key = $this->indexUsageCacheKey();

        return $this->usedIndexes = $this->usedIndexes ?? collect(Stache::cacheStore()->get($key, []));
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

        // Another process may have recorded usages since this one memoized its
        // list (e.g. parallel warming), so merge with the cache rather than replace it.
        $cached = collect(Stache::cacheStore()->get($key = $this->indexUsageCacheKey(), []));

        $this->usedIndexes = $cached->merge($indexes)->unique()->sort()->values();

        Stache::cacheStore()->put($key, $this->usedIndexes->all());
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
        if (! Stache::isWatcherEnabled()) {
            return;
        }

        // Get the existing files and timestamps from the cache.
        $cacheKey = "stache::timestamps::{$this->key()}";
        $existing = collect(Stache::cacheStore()->get($cacheKey, []));

        // Get the files and timestamps from the filesystem right now.
        $files = Traverser::filter([$this, 'getItemFilter'])->traverse($this);

        // Cache the files and timestamps, ready for comparisons on the next request.
        // We'll do it now since there are multiple early returns coming up.
        Stache::cacheStore()->forever($cacheKey, $files->all());

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
        $keys = $this->paths()->flip();
        $deleted = $existing->keys()
            ->map(fn ($path) => $keys->get($path))
            ->diff($files->keys()->map(fn ($path) => $keys->get($path)))
            ->values();

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

        if ($this->paths) {
            return $this->paths;
        }

        if ($paths = Stache::cacheStore()->get($this->pathsCacheKey())) {
            return $this->paths = collect($paths);
        }

        // An index value being worked out as the items are made can't be given the items.
        if ($this->makingItems && $this->filling !== null) {
            throw new ItemsNotReadyException;
        }

        $files = Traverser::filter([$this, 'getItemFilter'])->traverse($this);

        $this->makingItems = true;

        try {
            $paths = collect($this->makeAndCacheItemsOneAtATime($files));
        } finally {
            $this->makingItems = false;
        }

        $this->cachePaths($paths);

        Stache::cacheStore()->forever("stache::timestamps::{$this->key()}", $files->all());

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
        Stache::cacheStore()->forever($this->pathsCacheKey(), $paths->all());

        $this->paths = $paths;

        $this->cacheIndexUsage('path');
    }

    public function clearCachedPaths()
    {
        $this->paths = null;
        Stache::cacheStore()->forget($this->pathsCacheKey());
    }

    public function resetMemoizedState()
    {
        $this->paths = null;
    }

    protected function pathsCacheKey()
    {
        return "stache::indexes::{$this->key()}::path";
    }

    public function clear()
    {
        $cachedPaths = $this->paths ?? Stache::cacheStore()->get($this->pathsCacheKey(), []);

        collect($cachedPaths)->keys()->each(function ($key) {
            $this->forgetItem($key);
        });

        $this->resolveIndexes()->each(function ($index) {
            $index->clear();
            app('stache.indexes')->forget("{$this->key()}.{$index->name()}");
        });

        $this->usedIndexes = collect();
        Stache::cacheStore()->forget($this->indexUsageCacheKey());

        $this->clearCachedPaths();

        $this->keys()->clear();

        Stache::cacheStore()->forget("stache::timestamps::{$this->key()}");
    }

    /**
     * Each file is turned into an item and cached before the next one is read, so the
     * items don't all have to be in memory at once. While indexes are being filled,
     * an item that needs no other item to be indexed is indexed here and let go of.
     */
    private function makeAndCacheItemsOneAtATime($files): array
    {
        $paths = [];
        $indexAsMade = true;

        foreach ($files as $path => $timestamp) {
            $item = $this->makeItemFromFile($path, File::get($path));
            $key = $this->getItemKey($item);

            try {
                $this->keys()->add($key, $path);
            } catch (DuplicateKeyException $e) {
                Stache::duplicates()->track($this, $e->getKey(), $e->getPath());

                continue;
            }

            $this->cacheItem($item);
            $paths[$key] = $path;

            if ($this->filling === null) {
                continue;
            }

            if ($indexAsMade && $this->canIndexWithoutOtherItems($item)) {
                try {
                    $this->takeIndexValues($key, $item);
                } catch (ItemsNotReadyException $e) {
                    // Something an index needs (a mount, a computed value) went looking for another
                    // item of this store. The rest are likely to as well, so they wait their turn.
                    $indexAsMade = false;
                }
            }

            $this->releaseItem($key, $item);
        }

        return $paths;
    }

    /**
     * Whether an item's index values can be worked out before the rest of the store's items exist.
     */
    protected function canIndexWithoutOtherItems($item): bool
    {
        return false;
    }

    /**
     * Whether an index needs the store's other indexes to be there before it can be built.
     */
    protected function indexNeedsOtherIndexes($name): bool
    {
        return false;
    }

    /**
     * Called while indexes are being filled, once an item has been indexed or has been
     * cached to be indexed later, for a store that would otherwise hold on to its items.
     */
    protected function releaseItem($key, $item): void
    {
        //
    }

    private function takeIndexValues($key, $item): void
    {
        foreach ($this->filling['indexes'] as $name => $index) {
            $this->filling['values'][$name][$key] = $index->getItemValue($item);
        }

        $this->filling['done'][$key] = true;
    }

    /**
     * The indexes that get their values from one item at a time, and so can be filled
     * in a single pass over the items. An index that works out its items some
     * other way, or that needs the other indexes first, builds itself.
     */
    private function indexesFilledTogether($indexes)
    {
        return $indexes->filter(function ($index, $name) {
            return $index instanceof Indexes\Value
                && ! $this->indexNeedsOtherIndexes($name)
                && (static::$buildsItsOwnItems[$index::class] ??= (new ReflectionMethod($index, 'getItems'))->class !== Indexes\Value::class) === false;
        });
    }

    /**
     * Fills indexes from a single pass over the store's items, reading one item at a time,
     * rather than each index reading every item for itself.
     *
     * Asked for by an index that needs building, it fills that index along with any others
     * that haven't been built yet. Returns false if that index has to build itself.
     */
    public function fillIndexes(?Index $for = null): bool
    {
        // An index asked for while the pass is running is one that an item's value depends on.
        if ($this->filling !== null) {
            return false;
        }

        // A store that gathers its items in its own way is left to do so.
        if ((new ReflectionMethod($this, 'getItemsFromFiles'))->class !== self::class) {
            return false;
        }

        $indexes = $this->indexesFilledTogether($this->resolveIndexes());

        $all = $indexes->count();

        if ($for) {
            if (! $indexes->contains(fn ($index) => $index === $for)) {
                return false;
            }

            $indexes = $indexes->filter(fn ($index) => $index === $for || ! $index->isCached());
        }

        $this->filling = ['indexes' => $indexes->all(), 'values' => $indexes->map(fn () => [])->all(), 'done' => []];

        try {
            // On a cold Stache this is where the items are made, and most are indexed as they go.
            $paths = $this->paths();

            // The rest need other items to exist before they can be indexed. They all do now.
            Index::whileLoading($this->key().'/*', function () use ($paths) {
                foreach ($paths as $key => $path) {
                    if (isset($this->filling['done'][$key])) {
                        continue;
                    }

                    if ($item = $this->getItem($key)) {
                        $this->takeIndexValues($key, $item);
                        $this->releaseItem($key, $item);
                    }
                }
            });

            $filled = $this->filling;
        } finally {
            $this->filling = null;
        }

        foreach ($indexes as $name => $index) {
            // In the order of the paths, as it would be if the index had read every item itself.
            $index->fill(
                $paths->filter(fn ($path, $key) => isset($filled['done'][$key]))
                    ->map(fn ($path, $key) => $filled['values'][$name][$key])
                    ->all()
            );
        }

        // Only a pass that filled every one of them saves a warm from doing so.
        $this->filledDuringWarm = $indexes->count() === $all ? self::$warmRun : null;

        return true;
    }

    /**
     * Runs a warm of several stores. A store that another one needed
     * along the way will know it has already been dealt with.
     */
    public static function duringWarm(callable $callback)
    {
        self::$warmRun = uniqid('', true);

        try {
            return $callback();
        } finally {
            self::$warmRun = null;
        }
    }

    public function warm()
    {
        $indexes = $this->resolveIndexes();

        // Another store warmed before this one may have needed these
        // indexes, and they'll have been filled then.
        $filled = ($this->filledDuringWarm && $this->filledDuringWarm === self::$warmRun) || $this->fillIndexes();

        $this->shouldCacheFileItems = true;

        ($filled ? $indexes->diffKeys($this->indexesFilledTogether($indexes)) : $indexes)->each->update();

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
