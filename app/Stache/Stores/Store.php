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
    protected static $indexes = [];

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
        if (isset(static::$indexes[$this->key()][$name])) {
            return static::$indexes[$this->key()][$name];
        }

        $classes = array_merge($this->customIndexes, $this->defaultIndexes, $this->storeIndexes);

        $class = $classes[$name] ?? Indexes\Value::class;

        $index = new $class($this, $name);

        $index->load();

        static::$indexes[$this->key()][$name] = $index;

        return $index;
    }

    public function getItemsFromFiles()
    {
        $files = Traverser::filter([$this, 'getItemFilter'])->traverse($this);

        return $files->map(function ($timestamp, $path) {
            return $this->makeItemFromFile($path, File::get($path));
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
        $this->getStoreIndexes()->each->updateItem($item);
    }

    public function cacheIndexUsage($index)
    {
        $key = $this->indexUsageCacheKey();
        $index = $index->name();
        $indexes = collect(Cache::get($key, []));

        if ($indexes->contains($index)) {
            return;
        }

        $indexes->push($index);

        Cache::put($key, $indexes->all());
    }

    protected function indexUsageCacheKey()
    {
        return "stache::indexes::{$this->key()}::_indexes";
    }

    public function getStoreIndexes()
    {
        $indices = array_unique(array_merge(
            $this->defaultIndexes,
            $this->storeIndexes,
            config('statamic.stache.indexes', []),
            config("statamic.stache.stores.{$this->key()}.indexes", []),
            Cache::get($this->indexUsageCacheKey())
        ));

        return collect($indices)->map(function ($index, $key) {
            return (is_int($key))
                ? new Indexes\Value($this, $index)
                : new $index($this, $key);
        });
    }
}
