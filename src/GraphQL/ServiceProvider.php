<?php

namespace Statamic\GraphQL;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider as LaravelProvider;
use Statamic\Contracts\GraphQL\ResponseCache;
use Statamic\GraphQL\ResponseCache\DefaultCache;
use Statamic\GraphQL\ResponseCache\NullCache;
use Statamic\Http\Middleware\API\SwapExceptionHandler;
use Statamic\Http\Middleware\RequireStatamicPro;

class ServiceProvider extends LaravelProvider
{
    public function register()
    {
        $this->app->bind(ResponseCache::class, function ($app) {
            return config('statamic.graphql.cache') === false
                ? new NullCache
                : new DefaultCache;
        });

        $this->app->booting(function () {
            if ($this->hasPublishedConfig()) {
                return;
            }

            if (! config('statamic.graphql.enabled')) {
                config(['graphql.routes' => false]);
            }

            $this->addMiddleware();
            $this->disableGraphiql();
            $this->setDefaultSchema();
        });
    }

    public function boot()
    {
        Event::subscribe(Subscriber::class);
    }

    private function hasPublishedConfig()
    {
        return $this->app['files']->exists(config_path('graphql.php'));
    }

    private function addMiddleware()
    {
        $configKey = $this->isLegacyRebingGraphql()
            ? 'graphql.middleware'
            : 'graphql.route.middleware';

        config([$configKey => [
            SwapExceptionHandler::class,
            RequireStatamicPro::class,
        ]]);
    }

    private function disableGraphiql()
    {
        config(['graphql.graphiql.display' => false]);
    }

    private function setDefaultSchema()
    {
        config(['graphql.schemas.default' => DefaultSchema::class]);
    }

    protected function isLegacyRebingGraphql()
    {
        return class_exists('\Rebing\GraphQL\Support\ResolveInfoFieldsAndArguments');
    }
}
