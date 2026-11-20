<?php

namespace Statamic\Stache;

use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use Statamic\Assets\QueryBuilder as AssetQueryBuilder;
use Statamic\Facades\File;
use Statamic\Facades\Site;
use Statamic\Stache\Cache\CompiledStore;
use Statamic\Stache\Query\EntryQueryBuilder;
use Statamic\Stache\Query\SubmissionQueryBuilder;
use Statamic\Statamic;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\FlockStore;

class ServiceProvider extends LaravelServiceProvider
{
    private static bool $persistsAtShutdown = false;

    public function register()
    {
        $this->registerCompiledCacheStore();

        $this->app->singleton(Stache::class, function () {
            return (new Stache)->setLockFactory($this->locks());
        });

        $this->app->alias(Stache::class, 'stache');

        $this->app->singleton('stache.indexes', function () {
            return collect();
        });

        $this->app->bind(EntryQueryBuilder::class, function () {
            return new EntryQueryBuilder($this->app->make(Stache::class)->store('entries'));
        });

        $this->app->bind(AssetQueryBuilder::class, function () {
            return new AssetQueryBuilder($this->app->make(Stache::class)->store('assets'));
        });

        $this->app->bind(SubmissionQueryBuilder::class, function () {
            return new SubmissionQueryBuilder($this->app->make(Stache::class)->store('form-submissions'));
        });
    }

    public function boot()
    {
        $stache = $this->app->make(Stache::class);

        $stache->sites(Site::all()->keys()->all());

        $this->registerStores($stache);

        $this->resetMemoizedStateBetweenJobs($stache);

        $this->resetMemoizedStateBetweenOctaneRequests($stache);

        $this->persistCompiledCacheStore();
    }

    /**
     * Makes a cache store available for the Stache that keeps its contents as PHP files,
     * for OPcache to hold. It is only used when the Stache is configured to use it.
     */
    private function registerCompiledCacheStore()
    {
        if (! config()->has('cache.stores.stache_compiled')) {
            config(['cache.stores.stache_compiled' => [
                'driver' => 'stache_compiled',
                'path' => storage_path('statamic/stache-compiled'),
            ]]);
        }

        $this->callAfterResolving('cache', function ($cache) {
            $cache->extend('stache_compiled', function ($app, $config) use ($cache) {
                return $cache->repository(new CompiledStore($config['path'], $config['buckets'] ?? 32));
            });
        });
    }

    /**
     * The compiled store holds on to what it is given and writes it in one go, once
     * the response has been sent, the job is over, or the command has finished.
     */
    private function persistCompiledCacheStore()
    {
        $this->app->terminating(fn () => CompiledStore::persistAll());

        // A job run by a worker, that is. One run in the middle of a request is part of the request.
        Event::listen([JobProcessed::class, JobFailed::class], function () {
            if (Statamic::isWorker()) {
                CompiledStore::persistAll();
            }
        });

        if (! self::$persistsAtShutdown) {
            self::$persistsAtShutdown = true;

            register_shutdown_function(fn () => CompiledStore::persistAll());
        }
    }

    private function registerStores($stache)
    {
        // Merge the stores from our config file with the stores in the user's published
        // config file. If we ever need to add more stores, they won't need to add them.
        $config = require __DIR__.'/../../config/stache.php';
        $published = config('statamic.stache.stores');

        $nativeStores = collect($config['stores'])
            ->reject(fn ($config, $key) => $key === 'users' && config('statamic.users.repository') !== 'file')
            ->map(function ($config, $key) use ($published) {
                return array_merge($config, $published[$key] ?? []);
            });

        // Merge in any user defined stores that aren't native.
        $stores = $nativeStores->merge(collect($published)->diffKeys($nativeStores));

        $stores = $stores->map(function ($config) {
            return app($config['class'])->directory($config['directory'] ?? null);
        });

        $stache->registerStores($stores->all());
    }

    private function resetMemoizedStateBetweenJobs($stache)
    {
        Event::listen(JobProcessing::class, function () use ($stache) {
            if (! Statamic::isWorker()) {
                return;
            }

            CompiledStore::resetAll();

            $stache->stores()->each->resetMemoizedState();

            $this->app->make('stache.indexes')->each->resetMemoizedState();
        });
    }

    /**
     * The stores are made when the app boots, so under Octane a worker uses the same ones
     * for every request it handles. What they remember (their paths, for one) has to be
     * read again for each request, or the worker never sees what another process saves.
     */
    private function resetMemoizedStateBetweenOctaneRequests($stache)
    {
        // Listened for by name, since Octane isn't necessarily installed.
        Event::listen('Laravel\Octane\Events\RequestReceived', function () use ($stache) {
            CompiledStore::resetAll();

            $stache->stores()->each->resetMemoizedState();

            // Octane handles each request in its own copy of the container.
            app('stache.indexes')->each->resetMemoizedState();
        });
    }

    private function locks()
    {
        if (config('statamic.stache.lock.enabled', true)) {
            $store = $this->createFileLockStore();
        } else {
            $store = new NullLockStore;
        }

        return new LockFactory($store);
    }

    private function createFileLockStore()
    {
        File::makeDirectory($dir = storage_path('statamic/stache-locks'));

        return new FlockStore($dir);
    }
}
