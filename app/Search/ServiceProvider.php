<?php

namespace Statamic\Search;

use Statamic\API\Search;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use Statamic\Search\UpdateItemIndexes;

class ServiceProvider extends LaravelServiceProvider
{
    public function register()
    {
        $this->app->bind(IndexManager::class, function ($app) {
            return new IndexManager($app);
        });
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Commands\Insert::class,
                Commands\Update::class
            ]);
        }

        Event::subscribe(UpdateItemIndexes::class);
    }
}
