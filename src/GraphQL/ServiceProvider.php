<?php

namespace Statamic\GraphQL;

use Illuminate\Support\ServiceProvider as LaravelProvider;

class ServiceProvider extends LaravelProvider
{
    public function boot()
    {
        $this->app->booted(function () {
            $this->setDefaultSchema();
        });
    }

    private function setDefaultSchema()
    {
        config(['graphql.schemas.default' => DefaultSchema::config()]);
    }
}
