<?php

use Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Route;
use Statamic\Auth\Protect\Protectors\Password\Controller as PasswordProtectController;
use Statamic\Facades\OAuth;
use Statamic\Http\Controllers\ActivateAccountController;
use Statamic\Http\Controllers\ForgotPasswordController;
use Statamic\Http\Controllers\FormController;
use Statamic\Http\Controllers\FrontendController;
use Statamic\Http\Controllers\OAuthController;
use Statamic\Http\Controllers\PhoneHomeController;
use Statamic\Http\Controllers\ResetPasswordController;
use Statamic\Http\Controllers\TwoFactorChallengeController;
use Statamic\Http\Controllers\TwoFactorSetupController;
use Statamic\Http\Controllers\User\LoginController;
use Statamic\Http\Controllers\User\PasswordController;
use Statamic\Http\Controllers\User\ProfileController;
use Statamic\Http\Controllers\User\RegisterController;
use Statamic\Http\Middleware\AuthGuard;
use Statamic\Http\Middleware\CP\AuthGuard as CPAuthGuard;
use Statamic\Statamic;
use Statamic\StaticCaching\NoCache\Controller as NoCacheController;
use Statamic\StaticCaching\NoCache\NoCacheLocalize;

Route::name('statamic.')->group(function () {
    Route::group(['prefix' => config('statamic.routes.action')], function () {
        Route::post('forms/{form}', [FormController::class, 'submit'])->middleware([HandlePrecognitiveRequests::class])->name('forms.submit');

        Route::get('protect/password', [PasswordProtectController::class, 'show'])->name('protect.password.show');
        Route::post('protect/password', [PasswordProtectController::class, 'store'])->name('protect.password.store');

        Route::group(['prefix' => 'auth', 'middleware' => [AuthGuard::class]], function () {
            Route::get('logout', [LoginController::class, 'logout'])->name('logout');

            Route::group(['middleware' => [HandlePrecognitiveRequests::class]], function () {
                Route::post('login', [LoginController::class, 'login'])->name('login');
                Route::post('register', RegisterController::class)->name('register');
                Route::post('profile', ProfileController::class)->name('profile');
                Route::post('password', PasswordController::class)->name('password');
            });

            Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
            Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
            Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.reset.action');

            Route::get('two-factor-setup', TwoFactorSetupController::class)->name('two-factor-setup');
            Route::get('two-factor-challenge', [TwoFactorChallengeController::class, 'index'])->name('two-factor-challenge');
            Route::post('two-factor-challenge', [TwoFactorChallengeController::class, 'store']);
        });

        Route::group(['prefix' => 'auth', 'middleware' => [CPAuthGuard::class]], function () {
            Route::get('activate/{token}', [ActivateAccountController::class, 'showResetForm'])->name('account.activate');
            Route::post('activate', [ActivateAccountController::class, 'reset'])->name('account.activate.action');
        });

        Statamic::additionalActionRoutes();
    });

    Route::prefix(config('statamic.routes.action'))
        ->post('nocache', NoCacheController::class)
        ->middleware(NoCacheLocalize::class)
        ->withoutMiddleware(['App\Http\Middleware\VerifyCsrfToken', 'Illuminate\Foundation\Http\Middleware\VerifyCsrfToken']);

    if (OAuth::enabled()) {
        Route::get(config('statamic.oauth.routes.login'), [OAuthController::class, 'redirectToProvider'])->name('oauth.login');
        Route::match(['get', 'post'], config('statamic.oauth.routes.callback'), [OAuthController::class, 'handleProviderCallback'])
            ->withoutMiddleware(['App\Http\Middleware\VerifyCsrfToken', 'Illuminate\Foundation\Http\Middleware\VerifyCsrfToken'])
            ->name('oauth.callback');
    }
});

if (config('statamic.system.phone_home_route_enabled', true)) {
    Route::get('et/phone/home/{token}', PhoneHomeController::class)
        ->name('statamic.phone-home')
        ->middleware(ThrottleRequests::class.':1');
}

if (config('statamic.routes.enabled')) {
    Statamic::additionalWebRoutes();

    /*
     * Front-end
     * All front-end website requests go through a single controller method.
     */
    Route::any('/{segments?}', [FrontendController::class, 'index'])
        ->where('segments', Statamic::frontendRouteSegmentRegex())
        ->name('statamic.site');
}
