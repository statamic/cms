<?php

namespace Statamic\Git;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

class ServiceProvider extends LaravelServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                CommitCommand::class,
            ]);
        }

        Event::subscribe(Subscriber::class);
    }
}
