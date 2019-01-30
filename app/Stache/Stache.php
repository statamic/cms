<?php

namespace Statamic\Stache;

use Carbon\Carbon;
use Statamic\API\File;
use Statamic\API\Helper;
use Statamic\Stache\Stores\Store;
use Statamic\Extensions\FileStore;
use Illuminate\Support\Facades\Cache;

class Stache
{
    const TEMP_COLD = 'cold';
    const TEMP_WARM = 'warm';

    protected $bootstrapper;
    protected $shouldBoot = true;
    protected $booted = false;
    protected $temperature;
    protected $sites;
    protected $keys;
    protected $config;
    protected $stores;
    protected $startTime;

    public function __construct()
    {
        $this->temperature = SELF::TEMP_COLD;
        $this->stores = collect();
    }

    public function isCold()
    {
        return $this->temperature === self::TEMP_COLD;
    }

    public function isWarm()
    {
        return $this->temperature === self::TEMP_WARM;
    }

    public function heat()
    {
        $this->temperature = self::TEMP_WARM;
    }

    public function cool()
    {
        $this->temperature = self::TEMP_COLD;
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

    public function keys($keys = null)
    {
        if ($keys === null) {
            return $this->keys;
        }

        $this->keys = collect($keys);

        return $this;
    }

    public function config($config = null)
    {
        if (! $config) {
            return $this->config;
        }

        $this->config = $config;

        return $this;
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
        $this->boot();

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

    public function load()
    {
        (new Loader($this))->load();

        return $this;
    }

    public function update()
    {
        (new StacheUpdater($this))->update();

        return $this;
    }

    public function boot()
    {
        if ($this->shouldBoot && !$this->booted) {
            $this->booted = true;
            tap($this->bootstrapper ?? new Bootstrapper)->boot($this);
        }

        return $this;
    }

    public function setBootstrapper($bootstrapper)
    {
        $this->bootstrapper = $bootstrapper;

        return $this;
    }

    public function hasBooted()
    {
        return $this->booted;
    }

    public function withoutBooting($callback)
    {
        $this->disableBooting();
        $callback($this);
        $this->enableBooting();

        return $this;
    }

    public function disableBooting()
    {
        $this->shouldBoot = false;

        return $this;
    }

    public function enableBooting()
    {
        $this->shouldBoot = true;

        return $this;
    }

    public function generateId()
    {
        return Helper::makeUuid(); // TODO: Get prettier, or incremental IDs.
    }

    public function idMap()
    {
        return collect($this->stores())->mapWithKeys(function ($store) {
            return $store->getIdMap();
        });
    }

    public function getStoreById($id)
    {
        return $this->store($this->idMap()->get($id));
    }

    public function persist()
    {
        app(Persister::class)->persist();
    }

    public function clear()
    {
        // TODO: This is temporary. It wont work for other cache drivers like Redis.
        // We need to track all the cache keys, then loop through and forget them all.
        app('files')->deleteDirectory(base_path('storage/framework/cache/data/stache'));

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

    public function paths()
    {
        $paths = $this->sites()->mapWithKeys(function ($site) {
            return [$site => collect()];
        });

        foreach ($this->stores() as $store) {
            $storePaths = $store instanceof Stores\AggregateStore
                ? $this->getAggregateStorePaths($store)
                : $store->getPaths();

            $storeKey = $store->key();

            foreach ($storePaths as $site => $sitePaths) {
                $paths[$site] = $paths[$site]->merge($sitePaths->mapWithKeys(function ($path, $key) use ($storeKey) {
                    return ["{$storeKey}::{$key}" => $path];
                }));
            }
        }

        return $paths;
    }

    private function getAggregateStorePaths($store)
    {
        $paths = $this->sites()->mapWithKeys(function ($site) {
            return [$site => collect()];
        });

        foreach ($store->stores() as $store) {
            foreach ($store->getPaths() as $site => $sitePaths) {
                $paths[$site] = $paths[$site]->merge($sitePaths->mapWithKeys(function ($path, $key) use ($store) {
                    return ["{$store->childKey()}::{$key}" => $path];
                }));
            }
        }

        return $paths;
    }
}
