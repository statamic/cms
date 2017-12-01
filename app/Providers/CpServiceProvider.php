<?php

namespace Statamic\Providers;

use Illuminate\Support\ServiceProvider;

class CpServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if (! config('cp.enabled')) {
            return;
        }

        tap($this->app->make('view'), function ($view) {
            $view->composer('partials.nav-main', 'Statamic\Http\ViewComposers\NavigationComposer');
            $view->composer('layout', 'Statamic\Http\ViewComposers\PermissionComposer');
        });
    }

    public function register()
    {
        if (! config('cp.enabled')) {
            return;
        }

        //
    }
}
