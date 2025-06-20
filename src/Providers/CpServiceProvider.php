<?php

namespace Statamic\Providers;

use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Statamic\CP\Utilities\UtilityRepository;
use Statamic\Extensions\Translation\Loader;
use Statamic\Extensions\Translation\Translator;
use Statamic\Facades\User;
use Statamic\Fieldtypes\Sets;
use Statamic\Http\Middleware\CP\StartSession;
use Statamic\Http\View\Composers\CustomLogoComposer;
use Statamic\Http\View\Composers\FieldComposer;
use Statamic\Http\View\Composers\JavascriptComposer;
use Statamic\Http\View\Composers\NavComposer;
use Statamic\Http\View\Composers\SessionExpiryComposer;
use Statamic\Licensing\LicenseManager;
use Statamic\Licensing\Outpost;
use Statamic\Notifications\ElevatedSessionVerificationCode;
use Statamic\View\Components\OutsideLogo;

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

        Blade::component('outside-logo', OutsideLogo::class);

        Blade::directive('cp_svg', function ($expression) {
            return "<?php echo Statamic::svg({$expression}) ?>";
        });

        Blade::directive('rarr', function ($expression) {
            return "<?php echo Statamic::cpDirection() === 'ltr' ? '&rarr;' : '&larr;' ?>";
        });

        Sets::setIconsDirectory();

        $this->registerMiddlewareGroups();

        $this->registerElevatedSessionMacros();

        $this->bootSelfClosingUiTags();
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

        $this->app->singleton(StartSession::class, function ($app) {
            return new StartSession($app->make('session'), function () use ($app) {
                return $app->make('cache');
            });
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
            \Statamic\Http\Middleware\CP\TrimStrings::class,
        ]);

        $router->middlewareGroup('statamic.cp.authenticated', [
            \Statamic\Http\Middleware\CP\AuthenticateSession::class,
            \Statamic\Http\Middleware\CP\Authorize::class,
            \Statamic\Http\Middleware\CP\Localize::class,
            \Statamic\Http\Middleware\CP\SelectedSite::class,
            \Statamic\Http\Middleware\CP\BootPermissions::class,
            \Statamic\Http\Middleware\CP\BootPreferences::class,
            \Statamic\Http\Middleware\CP\BootUtilities::class,
            \Statamic\Http\Middleware\CP\CountUsers::class,
            \Statamic\Http\Middleware\CP\AddVaryHeaderToResponse::class,
            \Statamic\Http\Middleware\CP\RedirectIfTwoFactorSetupIncomplete::class,
            \Statamic\Http\Middleware\DeleteTemporaryFileUploads::class,
        ]);
    }

    private function registerElevatedSessionMacros()
    {
        Request::macro('hasElevatedSession', function () {
            return $this->getElevatedSessionExpiry() > now()->timestamp;
        });

        Request::macro('getElevatedSessionExpiry', function () {
            if (! $lastElevated = session()->get('statamic_elevated_session')) {
                return null;
            }

            return Carbon::createFromTimestamp($lastElevated)
                ->addMinutes(config('statamic.users.elevated_session_duration', 15))
                ->timestamp;
        });

        Request::macro('getElevatedSessionVerificationCode', function () {
            return session()->get('statamic_elevated_session_verification_code')['code'] ?? null;
        });

        Session::macro('elevate', function () {
            $this->put('statamic_elevated_session', now()->timestamp);
        });

        Session::macro('sendElevatedSessionVerificationCodeIfRequired', function () {
            if ($timestamp = session()->get('statamic_elevated_session_verification_code')['generated_at'] ?? null) {
                if ($timestamp > now()->subMinutes(5)->timestamp) {
                    return;
                }
            }

            $this->sendElevatedSessionVerificationCode();
        });

        Session::macro('sendElevatedSessionVerificationCode', function () {
            session()->put(
                key: 'statamic_elevated_session_verification_code',
                value: ['code' => $verificationCode = Str::random(20), 'generated_at' => now()->timestamp],
            );

            User::current()->notify(new ElevatedSessionVerificationCode($verificationCode));
        });
    }

    private function bootSelfClosingUiTags()
    {
        // Converts <ui-component /> to <ui-component></ui-component>
        Blade::prepareStringsForCompilationUsing(fn ($template) => str_contains($template, '<ui-')
            ? preg_replace_callback('/<(ui-[a-zA-Z0-9_-]+)([^>]*)\/>/', fn ($match) => "<{$match[1]}{$match[2]}></{$match[1]}>", $template)
            : $template);
    }
}
