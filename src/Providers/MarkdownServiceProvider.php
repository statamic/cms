<?php

namespace Statamic\Providers;

use Illuminate\Support\ServiceProvider;
use Statamic\Facades\Markdown;
use Statamic\Markdown\Manager;

class MarkdownServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Manager::class, function () {
            return new Manager;
        });
    }

    public function boot()
    {
        Markdown::extend('default', function ($parser) {
            return $parser->withStatamicDefaults();
        });
    }
}
