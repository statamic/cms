<?php

use Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests;
use Illuminate\Support\Facades\Route;
use Statamic\Auth\Protect\Protectors\Password\Controller as PasswordProtectController;
use Statamic\Facades\OAuth;
use Statamic\Http\Controllers\ActivateAccountController;
use Statamic\Http\Controllers\ForgotPasswordController;
use Statamic\Http\Controllers\FormController;
use Statamic\Http\Controllers\FrontendController;
use Statamic\Http\Controllers\OAuthController;
use Statamic\Http\Controllers\ResetPasswordController;
use Statamic\Http\Controllers\UserController;
use Statamic\Http\Middleware\AuthGuard;
use Statamic\Http\Middleware\CP\AuthGuard as CPAuthGuard;
use Statamic\Statamic;
use Statamic\StaticCaching\NoCache\Controller as NoCacheController;

Route::name('statamic.')->group(function () {
    Route::group(['prefix' => config('statamic.routes.action')], function () {
        Route::post('forms/{form}', [FormController::class, 'submit'])->middleware([HandlePrecognitiveRequests::class])->name('forms.submit');

        Route::get('protect/password', [PasswordProtectController::class, 'show'])->name('protect.password.show');
        Route::post('protect/password', [PasswordProtectController::class, 'store'])->name('protect.password.store');

        Route::group(['prefix' => 'auth', 'middleware' => [AuthGuard::class]], function () {
            Route::post('login', [UserController::class, 'login'])->name('login');
            Route::get('logout', [UserController::class, 'logout'])->name('logout');
            Route::post('register', [UserController::class, 'register'])->name('register');
            Route::post('profile', [UserController::class, 'profile'])->name('profile');
            Route::post('password', [UserController::class, 'password'])->name('password');

            Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
            Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
            Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.reset.action');
        });

        Route::group(['prefix' => 'auth', 'middleware' => [CPAuthGuard::class]], function () {
            Route::get('activate/{token}', [ActivateAccountController::class, 'showResetForm'])->name('account.activate');
            Route::post('activate', [ActivateAccountController::class, 'reset'])->name('account.activate.action');
        });

        Statamic::additionalActionRoutes();
    });

    Route::prefix(config('statamic.routes.action'))
        ->post('nocache', NoCacheController::class)
        ->withoutMiddleware('App\Http\Middleware\VerifyCsrfToken');

    if (OAuth::enabled()) {
        Route::get(config('statamic.oauth.routes.login'), [OAuthController::class, 'redirectToProvider'])->name('oauth.login');
        Route::get(config('statamic.oauth.routes.callback'), [OAuthController::class, 'handleProviderCallback'])->name('oauth.callback');
    }
});

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
