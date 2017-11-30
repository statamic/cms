<?php

namespace Statamic\Providers;

use Statamic\DataStore;
use Statamic\Extensions\FileStore;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // We have our own extension of Laravel's file-based cache driver.
        Cache::extend('statamic', function () {
            return Cache::repository(new FileStore(
                $this->app['files'],
                $this->app['config']["cache.stores.file"]['path']
            ));
        });

        view()->composer('partials.nav-main', 'Statamic\Http\ViewComposers\NavigationComposer');

    }

    public function register()
    {
        $this->app->singleton('Statamic\DataStore', function() {
            return new DataStore;
        });
    }
}
