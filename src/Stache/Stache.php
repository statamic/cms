<?php

namespace Statamic\Stache;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Concurrency;
use Statamic\Events\StacheCleared;
use Statamic\Events\StacheWarmed;
use Statamic\Extensions\FileStore;
use Statamic\Facades\File;
use Statamic\Stache\Stores\Store;
use Statamic\Support\Str;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\LockInterface;

class Stache
{
    protected $sites;
    protected $stores;
    protected $startTime;
    protected $updateIndexes = true;
    protected $lockFactory;
    protected $locks = [];
    protected $duplicates;
    protected $exclude = [];

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

    public function exclude(string $store)
    {
        $this->exclude[] = $store;

        return $this;
    }

    public function stores()
    {
        return $this->stores;
    }

    public function cacheStore()
    {
        return Cache::store(config('statamic.stache.cache_store'));
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
        $this->stores()->except($this->exclude)->reverse()->each->clear();

        $this->duplicates()->clear();

        $this->cacheStore()->forget('stache::timing');

        StacheCleared::dispatch();

        return $this;
    }

    public function refresh()
    {
        return $this->clear()->warm();
    }

    public function warm()
    {
        $lock = tap($this->lock('stache-warming'))->acquire(true);

        $this->startTimer();

        $stores = $this->stores()->except($this->exclude);

        if ($this->shouldUseParallelWarming($stores)) {
            $this->warmInParallel($stores);
        } else {
            $stores->each->warm();
        }

        $this->stopTimer();

        $lock->release();

        StacheWarmed::dispatch();
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

        $this->cacheStore()->forever('stache::timing', [
            'time' => floor((microtime(true) - $this->startTime) * 1000),
            'date' => Carbon::now()->timestamp,
        ]);

        return $this;
    }

    public function buildTime()
    {
        return $this->cacheStore()->get('stache::timing')['time'] ?? null;
    }

    public function buildDate()
    {
        if (! $cache = $this->cacheStore()->get('stache::timing')) {
            return null;
        }

        return Carbon::createFromTimestamp($cache['date'], config('app.timezone'));
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

    public function isWatcherEnabled(): bool
    {
        $config = config('statamic.stache.watcher');

        return $config === 'auto'
            ? app()->isLocal()
            : (bool) $config;
    }

    protected function shouldUseParallelWarming($stores): bool
    {
        $config = config('statamic.stache.warming', []);

        if (! ($config['parallel_processing'] ?? false)) {
            return false;
        }

        if ($stores->count() < ($config['min_stores_for_parallel'] ?? 3)) {
            return false;
        }

        if ($this->getCpuCoreCount() < 2) {
            return false;
        }

        // Disable parallel processing if using Redis cache (serialization issues)
        $cacheDriver = config('statamic.stache.cache_store', config('cache.default'));
        if ($cacheDriver === 'redis') {
            \Log::info('Parallel warming disabled due to Redis cache driver');

            return false;
        }

        return true;
    }

    protected function warmInParallel($stores)
    {
        try {
            $config = config('statamic.stache.warming', []);
            $maxProcesses = $config['max_processes'] ?? 0;

            if ($maxProcesses <= 0) {
                $maxProcesses = $this->getCpuCoreCount();
            }

            $maxProcesses = min($maxProcesses, $stores->count());

            $chunkSize = (int) ceil($stores->count() / $maxProcesses);
            $chunks = $stores->chunk($chunkSize);

            $closures = $chunks->map(function ($chunk) {
                return function () use ($chunk) {
                    return $chunk->each->warm()->keys()->all();
                };
            })->all();

            $driver = $config['concurrency_driver'] ?? 'process';

            if (empty($closures)) {
                \Log::info('Closures are empty, skipping parallel warming');
            }

            Concurrency::driver($driver)->run($closures);
        } catch (\Exception $e) {
            \Log::warning('Parallel warming failed, falling back to sequential: '.$e->getMessage());
            $stores->each->warm();
        }
    }

    protected function getCpuCoreCount(): int
    {
        if (! function_exists('shell_exec')) {
            return 1;
        }

        $command = match (PHP_OS_FAMILY) {
            'Windows' => 'echo %NUMBER_OF_PROCESSORS%',
            'Darwin' => 'sysctl -n hw.ncpu 2>/dev/null || echo 1',
            default => 'nproc 2>/dev/null || echo 1',
        };

        return max(1, (int) shell_exec($command));
    }
}
