<?php

namespace Statamic\Providers;

use Illuminate\Support\ServiceProvider;

class CpServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if (! config('statamic.cp.enabled')) {
            return;
        }

        tap($this->app->make('view'), function ($view) {
            $view->composer('statamic::partials.nav-main', 'Statamic\Http\ViewComposers\NavigationComposer');
            $view->composer('statamic::layout', 'Statamic\Http\ViewComposers\PermissionComposer');
            $view->composer('statamic::partials.scripts', 'Statamic\Http\ViewComposers\FieldtypeJsComposer');
        });
    }

    public function register()
    {
        if (! config('statamic.cp.enabled')) {
            return;
        }

        $this->registerPublishers();
    }

    /**
     * Register the Publisher's dependencies.
     *
     * @return void
     */
    private function registerPublishers()
    {
        $this->app->when(\Statamic\Http\Controllers\CP\PublishPageController::class)
                  ->needs(\Statamic\CP\Publish\Publisher::class)
                  ->give(\Statamic\CP\Publish\PagePublisher::class);

        $this->app->when(\Statamic\Http\Controllers\CP\PublishEntryController::class)
                  ->needs(\Statamic\CP\Publish\Publisher::class)
                  ->give(\Statamic\CP\Publish\EntryPublisher::class);

        $this->app->when(\Statamic\Http\Controllers\CP\PublishGlobalController::class)
                  ->needs(\Statamic\CP\Publish\Publisher::class)
                  ->give(\Statamic\CP\Publish\GlobalsPublisher::class);

        $this->app->when(\Statamic\Http\Controllers\CP\PublishTaxonomyController::class)
                  ->needs(\Statamic\CP\Publish\Publisher::class)
                  ->give(\Statamic\CP\Publish\TaxonomyPublisher::class);

        $this->app->when(\Statamic\Http\Controllers\CP\PublishUserController::class)
                  ->needs(\Statamic\CP\Publish\Publisher::class)
                  ->give(\Statamic\CP\Publish\UserPublisher::class);
    }
}
