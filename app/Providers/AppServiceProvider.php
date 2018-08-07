<?php

namespace Statamic\Providers;

use Statamic\DataStore;
use Statamic\Sites\Sites;
use Statamic\Routing\Router;
use Statamic\Extensions\FileStore;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    private $root = __DIR__.'/../..';

    public function boot()
    {
        // We have our own extension of Laravel's file-based cache driver.
        Cache::extend('statamic', function () {
            return Cache::repository(new FileStore(
                $this->app['files'],
                $this->app['config']["cache.stores.file"]['path']
            ));
        });

        $this->app[\Illuminate\Contracts\Http\Kernel::class]
             ->pushMiddleware(\Statamic\Http\Middleware\PermanentRedirects::class)
             ->pushMiddleware(\Statamic\Http\Middleware\VanityRedirects::class)
             ->pushMiddleware(\Statamic\Http\Middleware\PoweredByHeader::class);

        $this->app->booted(function () {
            $this->loadRoutesFrom("{$this->root}/routes/routes.php");
        });

        $this->loadViewsFrom("{$this->root}/resources/views", 'statamic');

        collect(['assets', 'cp', 'forms', 'routes', 'static_caching', 'sites', 'system', 'theming', 'users'])->each(function ($config) {
            $this->mergeConfigFrom("{$this->root}/config/$config.php", "statamic.$config");
            $this->publishes(["{$this->root}/config/$config.php" => config_path("statamic/$config.php")], 'statamic');
        });

        $this->publishes([
            "{$this->root}/resources/dist" => public_path('resources/cp')
        ], 'statamic');
    }

    public function register()
    {
        $this->app->singleton('Statamic\DataStore', function() {
            return new DataStore;
        });

        $this->app->bind(Router::class, function () {
            return new Router(config('statamic.routes.routes', []));
        });

        $this->app->singleton(Sites::class, function () {
            return new Sites(config('statamic.sites'));
        });

        $this->app->bind(
            \Statamic\Contracts\Data\Repositories\CollectionRepository::class,
            \Statamic\Stache\Repositories\CollectionRepository::class
        );
    }
}
