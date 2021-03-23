<?php

namespace Statamic\Stache;

use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use Statamic\Assets\QueryBuilder as AssetQueryBuilder;
use Statamic\Facades\File;
use Statamic\Facades\Site;
use Statamic\Stache\Query\EntryQueryBuilder;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\FlockStore;

class ServiceProvider extends LaravelServiceProvider
{
    public function register()
    {
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
    }

    public function boot()
    {
        $stache = $this->app->make(Stache::class);

        $stache->sites(Site::all()->keys()->all());

        $this->registerStores($stache);
    }

    private function registerStores($stache)
    {
        // Merge the stores from our config file with the stores in the user's published
        // config file. If we ever need to add more stores, they won't need to add them.
        $config = require __DIR__.'/../../config/stache.php';
        $published = config('statamic.stache.stores');

        $nativeStores = collect($config['stores'])
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
