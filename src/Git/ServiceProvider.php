<?php

namespace Statamic\Git;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use Statamic\Statamic;

class ServiceProvider extends LaravelServiceProvider
{
    public function boot()
    {
        if (! Statamic::pro()) {
            return;
        }

        if ($this->app->runningInConsole()) {
            $this->commands([
                CommitCommand::class,
            ]);
        }

        Event::subscribe(Subscriber::class);
    }
}
