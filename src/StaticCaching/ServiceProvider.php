<?php

namespace Statamic\StaticCaching;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use Illuminate\Support\Str;
use Statamic\Contracts\View\Antlers\Parser as ParserContract;
use Statamic\StaticCaching\NoCache\NoCacheManager;
use Statamic\View\Cascade;

class ServiceProvider extends LaravelServiceProvider
{
    public function register()
    {
        $this->registerNoCache();

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
    }

    private function registerNoCache()
    {
        $this->app->singleton(ResponseReplacer::class, function ($app) {
            return new ResponseReplacer(config('statamic.static_caching.replacers', []));
        });

        $this->app->singleton(NoCacheManager::class, function ($app) {
            $cacheDirectory = storage_path('framework/cache/data/_nocache');

            if (! file_exists($cacheDirectory)) {
                mkdir($cacheDirectory, 0755, true);
            }

            $cacheDirectory = Str::finish($cacheDirectory, '/');

            return new NoCacheManager(
                config()->all(),
                $cacheDirectory,
                $app[ParserContract::class],
                $app[Cascade::class],
                $app[Filesystem::class]
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
