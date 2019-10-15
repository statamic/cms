<?php

namespace Statamic\Providers;

use Facades\Statamic\Fields\FieldtypeRepository;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Statamic\CP\Utilities\CoreUtilities;
use Statamic\CP\Utilities\UtilityRepository;
use Statamic\Extensions\Translation\Loader;
use Statamic\Extensions\Translation\Translator;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use Statamic\Http\View\Composers\FieldComposer;
use Statamic\Http\View\Composers\SessionExpiryComposer;
use Statamic\Statamic;

class CpServiceProvider extends ServiceProvider
{
    public function boot()
    {
        View::composer('statamic::*', function ($view) {
            $view->with('user', User::current());
        });

        View::composer(FieldComposer::VIEWS, FieldComposer::class);
        View::composer(SessionExpiryComposer::VIEWS, SessionExpiryComposer::class);

        View::composer('statamic::layout', function ($view) {
            Statamic::provideToScript([
                'translationLocale' => $this->app['translator']->locale(),
                'translations' => $this->app['translator']->toJson(),
                'sites' => $this->sites(),
                'selectedSite' => Site::selected()->handle(),
                'ampEnabled' => config('statamic.amp.enabled'),
                'preloadableFieldtypes' => FieldtypeRepository::preloadable()->keys(),
                'livePreview' => config('statamic.live_preview'),
                'locale' => config('app.locale'),
                'permissions' => base64_encode(json_encode(User::current()->permissions()))
            ]);
        });

        CoreUtilities::boot();
    }

    protected function sites()
    {
        return Site::all()->map(function ($site) {
            return [
                'name' => $site->name(),
                'handle' => $site->handle(),
            ];
        })->values();
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
