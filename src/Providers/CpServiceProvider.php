<?php

namespace Statamic\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Statamic\CP\Utilities\UtilityRepository;
use Statamic\Extensions\Translation\Loader;
use Statamic\Extensions\Translation\Translator;
use Statamic\Facades\User;
use Statamic\Http\View\Composers\CustomLogoComposer;
use Statamic\Http\View\Composers\FieldComposer;
use Statamic\Http\View\Composers\JavascriptComposer;
use Statamic\Http\View\Composers\NavComposer;
use Statamic\Http\View\Composers\SessionExpiryComposer;
use Statamic\Licensing\LicenseManager;
use Statamic\Licensing\Outpost;

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
        View::composer(NavComposer::VIEWS, NavComposer::class);
        View::composer(CustomLogoComposer::VIEWS, CustomLogoComposer::class);

        Blade::directive('cp_svg', function ($expression) {
            return "<?php echo Statamic::svg({$expression}) ?>";
        });

        $this->registerMiddlewareGroups();
    }

    public function register()
    {
        $this->app->extend('translation.loader', function ($loader, $app) {
            return new Loader($loader, $app['path.lang']);
        });

        $this->app->extend('translator', function ($translator, $app) {
            $extended = new Translator($app['files'], $translator->getLoader(), $translator->getLocale());
            $extended->setFallback($translator->getFallback());

            return $extended;
        });

        $this->app->singleton(UtilityRepository::class, function () {
            return new UtilityRepository;
        });

        $this->app->singleton(LicenseManager::class, function ($app) {
            return new LicenseManager($app[Outpost::class]);
        });
    }

    protected function registerMiddlewareGroups()
    {
        $router = $this->app->make(Router::class);

        $router->middlewareGroup('statamic.cp', [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Statamic\Http\Middleware\CP\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \Statamic\Http\Middleware\CP\ContactOutpost::class,
            \Statamic\Http\Middleware\CP\AuthGuard::class,
            \Statamic\Http\Middleware\CP\AddToasts::class,
        ]);

        $router->middlewareGroup('statamic.cp.authenticated', [
            \Statamic\Http\Middleware\CP\Authorize::class,
            \Statamic\Http\Middleware\CP\Localize::class,
            \Statamic\Http\Middleware\CP\BootPermissions::class,
            \Statamic\Http\Middleware\CP\BootPreferences::class,
            \Statamic\Http\Middleware\CP\BootUtilities::class,
            \Statamic\Http\Middleware\CP\CountUsers::class,
            \Statamic\Http\Middleware\DeleteTemporaryFileUploads::class,
        ]);
    }
}
