<?php

namespace Statamic\StaticCaching;

use Illuminate\Support\Facades\Event;
use Statamic\StaticCaching\Invalidator;
use Statamic\StaticCaching\Middleware\Retrieve;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

class ServiceProvider extends LaravelServiceProvider
{
    protected $defer = true;

    public function register()
    {
        $this->app->singleton(StaticCacheManager::class, function ($app) {
            return new StaticCacheManager($app);
        });

        $this->app->bind(Cacher::class, function ($app) {
            return $app[StaticCacheManager::class]->driver();
        });

        $this->app->bind(Invalidator::class, function ($app) {
            $class = config('statamic.static_caching.invalidation.class');

            return new $class(
                $app[Cacher::class],
                $app['config']['statamic.static_caching.invalidation']
            );
        });
    }

    public function boot()
    {
        $this->app['router']->prependMiddlewareToGroup('web', Retrieve::class);

        Event::subscribe(Invalidate::class);

        $this->commands(ClearStaticCommand::class);
    }

    public function provides()
    {
        return [StaticCacheManager::class, Cacher::class];
    }
}
