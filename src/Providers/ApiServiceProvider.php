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

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../../resources/graphiql' => public_path('vendor/statamic/graphiql'),
        ], 'statamic-graphiql');
    }
}
