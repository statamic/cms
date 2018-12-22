<?php

namespace Statamic\Providers;

use Statamic\Extensions\FileStore;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;

class CacheServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->extendFileStore();
        $this->macroRememberWithExpiration();
    }

    /**
     * We have our own extension of Laravel's file-based cache driver.
     *
     * @return void
     */
    private function extendFileStore()
    {
        Cache::extend('statamic', function () {
            return Cache::repository(new FileStore(
                $this->app['files'],
                $this->app['config']["cache.stores.file"]['path']
            ));
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
            $keyValuePair = $callback();
            $value = reset($keyValuePair);
            $expirationInMinutes = key($keyValuePair);

            return Cache::remember($cacheKey, $expirationInMinutes, function () use ($value) {
                return $value;
            });
        });
    }
}
