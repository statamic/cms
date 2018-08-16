<?php

namespace Statamic\Stache;

use Statamic\API\Site;
use Statamic\Stache\Stache;
use Statamic\Stache\Stores;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

class ServiceProvider extends LaravelServiceProvider
{
    public function register()
    {
        $this->app->singleton(Stache::class, function () {
            return new Stache;
        });

        $this->app->alias(Stache::class, 'stache');
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
