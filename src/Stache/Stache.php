<?php

namespace Statamic\Stache;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Statamic\Extensions\FileStore;
use Statamic\Facades\File;
use Statamic\Stache\Stores\AggregateStore;
use Statamic\Stache\Stores\Store;
use Statamic\Support\Str;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\LockInterface;
use WeakMap;
use Wilderborn\Partyline\Facade as Partyline;

class Stache
{
    protected $sites;
    protected $stores;
    protected $startTime;
    protected $updateIndexes = true;
    protected $lockFactory;
    protected $locks = [];
    protected $duplicates;
    protected $indexedValuesAllowed = true;
    protected $indexReferences = [];
    protected $dependantIndexClasses = [];
    protected $dependantIndexes = [];

    public function __construct()
    {
        $this->stores = collect();
    }

    protected function registerDependantIndexes($item)
    {
        $class = get_class($item);

        if (array_key_exists($class, $this->dependantIndexClasses)) {
            return;
        }

        // Prevent registering the same class multiple times.
        $this->dependantIndexClasses[$class] = true;

        $dependencies = $item->getDependantIndexes();

        foreach ($dependencies as $store => $indexNames) {
            if (! array_key_exists($store, $this->dependantIndexes)) {
                $this->dependantIndexes[$store] = [];
            }

            $this->dependantIndexes[$store] = array_merge($this->dependantIndexes[$store], $indexNames);
        }
    }

    public function updateDependantIndexes($store, $handle)
    {
        if (! array_key_exists($store, $this->dependantIndexes)) {
            return;
        }

        $this->withoutIndexedValues(function () use ($store, $handle) {
            $storeInstance = $this->store($store);
            foreach ($this->dependantIndexes[$store] as $index) {
                if ($storeInstance instanceof AggregateStore) {
                    $storeInstance->store($handle)->index($index)->update();
                } else {
                    $storeInstance->index($index)->update();
                }
            }
        });
    }

    public function itemUsingIndexValues($index, $item)
    {
        $this->registerDependantIndexes($item);

        if (! array_key_exists($index, $this->indexReferences)) {
            $this->indexReferences[$index] = new WeakMap();
        }

        $this->indexReferences[$index][$item] = 1;
    }

    public function flushIndexValues($index)
    {
        if (! array_key_exists($index, $this->indexReferences)) {
            return;
        }

        foreach ($this->indexReferences[$index] as $item => $value) {
            if (! method_exists($item, 'flushIndexedValue')) {
                continue;
            }

            $item->flushIndexedValue($index);
        }
    }

    public function shouldUseIndexValues()
    {
        return $this->indexedValuesAllowed;
    }

    public function setShouldUseIndexValues($allowed = true)
    {
        $this->indexedValuesAllowed = $allowed;

        return $this;
    }

    public function withoutIndexedValues(callable $callback)
    {
        $currentSetting = $this->shouldUseIndexValues();

        $this->setShouldUseIndexValues(false);

        $result = $callback();

        $this->setShouldUseIndexValues($currentSetting);

        return $result;
    }

    public function sites($sites = null)
    {
        if (! $sites) {
            return $this->sites;
        }

        $this->sites = collect($sites);

        return $this;
    }

    public function defaultSite()
    {
        return $this->sites->first();
    }

    public function registerStore(Store $store)
    {
        $this->stores->put($store->key(), $store);

        return $this;
    }

    public function registerStores($stores)
    {
        foreach ($stores as $store) {
            $this->registerStore($store);
        }

        return $this;
    }

    public function stores()
    {
        return $this->stores;
    }

    public function store($key)
    {
        if (Str::contains($key, '::')) {
            [$parent, $child] = explode('::', $key);

            return $this->stores()->get($parent)->store($child);
        }

        return $this->stores()->get($key);
    }

    public function generateId()
    {
        return (string) Str::uuid();
    }

    public function clear()
    {
        Partyline::comment('Clearing Stache...');

        $this->stores()->reverse()->each->clear();

        $this->duplicates()->clear();

        Cache::forget('stache::timing');

        return $this;
    }

    public function refresh()
    {
        return $this->clear()->warm();
    }

    public function warm()
    {
        Partyline::comment('Warming Stache...');

        $lock = tap($this->lock('stache-warming'))->acquire(true);

        $this->startTimer();

        $this->stores()->each->warm();

        $this->stopTimer();

        $lock->release();
    }

    public function instance()
    {
        return $this;
    }

    public function fileCount()
    {
        return $this->stores()->reduce(function ($carry, $store) {
            return $store->paths()->count() + $carry;
        }, 0);
    }

    public function fileSize()
    {
        if (! ($cache = app('cache')->store()->getStore()) instanceof FileStore) {
            return null;
        }

        $files = File::getFiles($cache->getDirectory().'/stache', true);

        return collect($files)->reduce(function ($size, $path) {
            return $size + File::size($path);
        }, 0);
    }

    public function startTimer()
    {
        $this->startTime = microtime(true);

        return $this;
    }

    public function stopTimer()
    {
        if (! $this->startTime) {
            return $this;
        }

        Cache::forever('stache::timing', [
            'time' => floor((microtime(true) - $this->startTime) * 1000),
            'date' => Carbon::now()->timestamp,
        ]);

        return $this;
    }

    public function buildTime()
    {
        return Cache::get('stache::timing')['time'] ?? null;
    }

    public function buildDate()
    {
        if (! $cache = Cache::get('stache::timing')) {
            return null;
        }

        return Carbon::createFromTimestamp($cache['date']);
    }

    public function disableUpdatingIndexes()
    {
        $this->updateIndexes = false;

        return $this;
    }

    public function shouldUpdateIndexes()
    {
        return $this->updateIndexes;
    }

    public function setLockFactory(LockFactory $lockFactory)
    {
        $this->lockFactory = $lockFactory;

        return $this;
    }

    public function lock($name): LockInterface
    {
        if (isset($this->locks[$name])) {
            return $this->locks[$name];
        }

        return $this->locks[$name] = $this->lockFactory->createLock($name);
    }

    public function duplicates()
    {
        if ($this->duplicates) {
            return $this->duplicates;
        }

        return $this->duplicates = (new Duplicates($this))->load();
    }
}
