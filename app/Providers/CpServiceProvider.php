<?php

namespace Statamic\Providers;

use Statamic\Statamic;
use Illuminate\Support\ServiceProvider;
use Statamic\Extensions\Translation\Translator;

class CpServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->preventRegistration()) {
            return;
        }

        tap($this->app->make('view'), function ($view) {
            $view->composer('statamic::partials.nav-main', 'Statamic\Http\ViewComposers\NavigationComposer');
            $view->composer('statamic::layout', 'Statamic\Http\ViewComposers\PermissionComposer');
        });
    }

    public function register()
    {
        if ($this->preventRegistration()) {
            return;
        }

        $this->registerPublishers();

        $this->app->extend('translator', function ($translator) {
            return new Translator($translator->getLoader(), $translator->getLocale());
        });
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

    private function preventRegistration()
    {
        if (app()->environment('testing')) {
            return false;
        }

        return ! Statamic::isCpRoute();
    }
}
