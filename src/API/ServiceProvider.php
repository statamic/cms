<?php

namespace Statamic\API;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use Statamic\Http\Middleware\HandleToken;
use Statamic\Http\Resources\API\Resource;

class ServiceProvider extends LaravelServiceProvider
{
    /**
     * Register API resources.
     *
     * @return void
     */
    public function register()
    {
        Resource::mapDefaults();

        $this->app->singleton(ApiCacheManager::class, function ($app) {
            return new ApiCacheManager($app);
        });

        $this->app->bind(Cacher::class, function ($app) {
            return $app[ApiCacheManager::class]->driver();
        });
    }

    public function boot()
    {
        Event::subscribe(Subscriber::class);

        $this->app[Kernel::class]->appendMiddlewareToGroup(config('statamic.api.middleware'), HandleToken::class);
    }
}
