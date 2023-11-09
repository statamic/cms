<?php

namespace Statamic\Tasks;

use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use Spatie\Fork\Fork;

class ServiceProvider extends LaravelServiceProvider
{
    public function register()
    {
        $this->app->bind('fork-installed', fn () => class_exists(Fork::class));

        $this->app->bind(Tasks::class, function ($app) {
            return $app['fork-installed']
                ? new ConcurrentTasks(new Fork)
                : new ConsecutiveTasks;
        });
    }
}
