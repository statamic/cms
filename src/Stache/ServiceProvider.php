<?php

namespace Statamic\Stache;

use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use Statamic\Facades\Site;

class ServiceProvider extends LaravelServiceProvider
{
    public function register()
    {
        $this->app->singleton(Stache::class, function () {
            return new Stache;
        });

        $this->app->alias(Stache::class, 'stache');

        $this->app->singleton('stache.indexes', function () {
            return collect();
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
}
