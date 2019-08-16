<?php

namespace Statamic\Stache\Stores;

use Statamic\API\File;
use Statamic\Stache\Indexes;
use Facades\Statamic\Stache\Traverser;

abstract class Store
{
    protected $directory;
    protected $customIndexes = [];
    protected $defaultIndexes = [
        'id' => Indexes\Id::class,
        'site' => Indexes\Site::class,
    ];
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

        $classes = array_merge($this->customIndexes, $this->defaultIndexes);

        $class = $classes[$name] ?? Indexes\Value::class;

        $index = new $class($this, $name);

        $index->load();

        static::$indexes[$this->key()][$name] = $index;

        return $index;
    }

    public function getItemsFromFiles()
    {
        return Traverser::traverse($this)->map(function ($timestamp, $path) {
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
}