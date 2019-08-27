<?php

namespace Statamic\Stache;

use Carbon\Carbon;
use Statamic\API\Str;
use Statamic\API\File;
use Statamic\API\Helper;
use Statamic\Stache\Stores\Store;
use Statamic\Extensions\FileStore;
use Illuminate\Support\Facades\Cache;

class Stache
{
    protected $sites;
    protected $stores;
    protected $startTime;

    public function __construct()
    {
        $this->stores = collect();
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
        if (str_contains($key, '::')) {
            list($parent, $child) = explode('::', $key);
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
        $this->stores()->each->clear();

        return $this;
    }

    public function refresh()
    {
        $this->clear()->startTimer()->update()->persist();
    }

    public function instance()
    {
        return $this;
    }

    public function fileCount()
    {
        return $this->paths()->flatten()->count();
    }

    public function fileSize()
    {
        if (! ($cache = app('cache')->store()->getStore()) instanceof FileStore) {
            return null;
        }

        $files = File::getFiles($cache->getDirectory() . '/stache', true);

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
            'date' => Carbon::now()->timestamp
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
        };

        return Carbon::createFromTimestamp($cache['date']);
    }
}
