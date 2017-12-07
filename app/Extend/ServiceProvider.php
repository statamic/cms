<?php

namespace Statamic\Extend;

use Statamic\API\Helper;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

abstract class ServiceProvider extends LaravelServiceProvider
{
    /**
     * Provides access to addon helper methods
     */
    use Extensible;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    public function registerEventListener($class)
    {
        $listener = $this->app->make($class);

        foreach ($listener->events as $event => $methods) {
            foreach (Helper::ensureArray($methods) as $method) {
                Event::listen($event, [$listener, $method]);
            }
        }
    }
}
