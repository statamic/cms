<?php

namespace Statamic\Stache;

use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
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
    }

    public function boot()
    {
        $stache = $this->app->make(Stache::class);

        $stache->sites(Site::all()->keys()->all());

        $stache->registerStores(collect(config('statamic.stache.stores'))->map(function ($config) {
            return app($config['class'])->directory($config['directory']);
        })->all());
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
