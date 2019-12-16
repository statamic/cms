<?php

namespace Statamic\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Statamic\CP\Utilities\CoreUtilities;
use Statamic\CP\Utilities\UtilityRepository;
use Statamic\Extensions\Translation\Loader;
use Statamic\Extensions\Translation\Translator;
use Statamic\Facades\User;
use Statamic\Http\View\Composers\FieldComposer;
use Statamic\Http\View\Composers\JavascriptComposer;
use Statamic\Http\View\Composers\SessionExpiryComposer;

class CpServiceProvider extends ServiceProvider
{
    public function boot()
    {
        View::composer('statamic::*', function ($view) {
            $view->with('user', User::current());
        });

        View::composer(FieldComposer::VIEWS, FieldComposer::class);
        View::composer(SessionExpiryComposer::VIEWS, SessionExpiryComposer::class);
        View::composer(JavascriptComposer::VIEWS, JavascriptComposer::class);

        CoreUtilities::boot();
    }

    public function register()
    {
        $this->app->extend('translation.loader', function ($loader, $app) {
            return new Loader($loader, $app['path.lang']);
        });

        $this->app->extend('translator', function ($translator, $app) {
            return new Translator($app['files'], $translator->getLoader(), $translator->getLocale());
        });

        $this->app->singleton(UtilityRepository::class, function () {
            return new UtilityRepository;
        });
    }
}
