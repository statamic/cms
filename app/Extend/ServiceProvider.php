<?php

namespace Statamic\Extend;

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
}
