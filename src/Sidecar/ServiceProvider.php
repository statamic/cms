<?php

namespace Statamic\Sidecar;

use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use Statamic\Statamic;

class ServiceProvider extends LaravelServiceProvider
{
    public function register()
    {
        $this->app->singleton(Manager::class, function () {
            return new Manager;
        });
    }

    public function boot()
    {
        Statamic::booted(function () {
            $this->app->make(Manager::class)->boot();
        });
    }
}
