<?php

namespace Statamic\Providers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use Statamic\Extensions\FileStore;

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
                $this->app['config']['cache.stores.file']['path']
            ));
        });

        if (config('cache.default') === 'file') {
            config(['cache.stores.statamic' => ['driver' => 'statamic']]);
            config(['cache.default' => 'statamic']);
        }
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
            $expiration = Carbon::now()->addMinutes(key($keyValuePair));

            return Cache::remember($cacheKey, $expiration, function () use ($value) {
                return $value;
            });
        });
    }
}
