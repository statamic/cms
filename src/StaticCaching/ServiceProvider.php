<?php

namespace Statamic\StaticCaching;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

class ServiceProvider extends LaravelServiceProvider
{
    public function register()
    {
        $this->app->singleton(StaticCacheManager::class, function ($app) {
            return new StaticCacheManager($app);
        });

        $this->app->bind(Cacher::class, function ($app) {
            return $app[StaticCacheManager::class]->driver();
        });

        $this->app->bind(Invalidator::class, function ($app) {
            $class = config('statamic.static_caching.invalidation.class') ?? DefaultInvalidator::class;

            return $app[$class];
        });

        $this->app->bind(DefaultInvalidator::class, function ($app) {
            return new DefaultInvalidator(
                $app[Cacher::class],
                $app['config']['statamic.static_caching.invalidation.rules']
            );
        });

        $this->app->bind(UrlExcluder::class, function ($app) {
            $class = config('statamic.static_caching.exclude.class') ?? DefaultUrlExcluder::class;

            return $app[$class];
        });

        $this->app->bind(DefaultUrlExcluder::class, function ($app) {
            $config = $app['config']['statamic.static_caching.exclude'];

            // Before the urls sub-array was introduced, you could define
            // the urls to be excluded at the top "exclude" array level.
            $urls = $config['urls'] ?? $config;

            return new DefaultUrlExcluder(
                $app[Cacher::class]->getBaseUrl(),
                $urls
            );
        });
    }

    public function boot()
    {
        if (config('statamic.static_caching.strategy')) {
            Event::subscribe(Invalidate::class);
        }
    }
}
