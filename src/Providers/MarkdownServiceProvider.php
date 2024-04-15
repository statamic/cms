<?php

namespace Statamic\Providers;

use Illuminate\Support\ServiceProvider;
use Statamic\Markdown\Manager;

class MarkdownServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Manager::class, function () {
            return new Manager;
        });
    }
}
