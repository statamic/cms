<?php

namespace Statamic\API;

use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
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
    }
}
