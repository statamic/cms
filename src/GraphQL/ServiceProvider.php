<?php

namespace Statamic\GraphQL;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider as LaravelProvider;
use Rebing\GraphQL\GraphQLController;
use Statamic\Contracts\GraphQL\ResponseCache;
use Statamic\GraphQL\ResponseCache\DefaultCache;
use Statamic\GraphQL\ResponseCache\NullCache;
use Statamic\Http\Middleware\HandleToken;
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
                $this->disableGraphqlRoutes();
            }

            $this->disableGraphiql();
            $this->setDefaultSchema();
        });
    }

    public function boot()
    {
        Event::subscribe(Subscriber::class);

        $this->app->booted(fn () => $this->addMiddleware());
    }

    private function hasPublishedConfig()
    {
        return $this->app['files']->exists(config_path('graphql.php'));
    }

    private function disableGraphqlRoutes()
    {
        config(['graphql.route' => false]);
    }

    private function addMiddleware()
    {
        collect($this->app['router']->getRoutes()->getRoutes())
            ->filter(fn ($route) => $route->getAction()['uses'] === GraphQLController::class.'@query')
            ->each(fn ($route) => $route->middleware([
                RequireStatamicPro::class,
                HandleToken::class,
            ]));
    }

    private function disableGraphiql()
    {
        config(['graphql.graphiql.display' => false]);
    }

    private function setDefaultSchema()
    {
        config(['graphql.schemas.default' => DefaultSchema::class]);
    }
}
