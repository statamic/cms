<?php

namespace Statamic\Providers;

use Illuminate\Support\ServiceProvider;
use Statamic\Http\Resources\API\Resource;

class ApiServiceProvider extends ServiceProvider
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
