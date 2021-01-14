<?php

namespace Statamic\GraphQL;

use Illuminate\Support\ServiceProvider as LaravelProvider;

class ServiceProvider extends LaravelProvider
{
    public function register()
    {
        $this->app->booting(function () {
            if ($this->hasPublishedConfig()) {
                return;
            }

            $this->disableGraphiql();
            $this->setDefaultSchema();
        });
    }

    private function hasPublishedConfig()
    {
        return $this->app['files']->exists(config_path('graphql.php'));
    }

    private function disableGraphiql()
    {
        config(['graphql.graphiql.display' => false]);
    }

    private function setDefaultSchema()
    {
        config(['graphql.schemas.default' => DefaultSchema::config()]);
    }
}
