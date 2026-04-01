<?php

namespace Statamic\Providers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use Statamic\Extensions\FileStore;

class CacheServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->extendFileStore();
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->macroRememberWithExpiration();
    }

    /**
     * We have our own extension of Laravel's file-based cache driver.
     *
     * @return void
     */
    private function extendFileStore()
    {
        $this->app->booting(function () {
            // Don't extend the file store if it's already being extended.
            $creators = (fn () => $this->customCreators)->call(Cache::getFacadeRoot());
            if (isset($creators['file'])) {
                return;
            }

            Cache::extend('file', function ($app, $config) {
                return Cache::repository(
                    (new FileStore($app['files'], $config['path'], $config['permission'] ?? null))
                        ->setLockDirectory($config['lock_path'] ?? null),
                    $config
                );
            });
        });
    }

    /**
     * Macro rememberWithExpiration() onto Cache.
     *
     * @return void
     */
    private function macroRememberWithExpiration()
    {
        Cache::macro('rememberWithExpiration', function ($cacheKey, $callback) {
            if (Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }

            $keyValuePair = $callback();
            $value = reset($keyValuePair);
            $expiration = Carbon::now()->addMinutes((int) key($keyValuePair));

            return Cache::remember($cacheKey, $expiration, function () use ($value) {
                return $value;
            });
        });
    }
}
