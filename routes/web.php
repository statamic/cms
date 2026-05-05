<?php

use Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Route;
use Statamic\Auth\Protect\Protectors\Password\Controller as PasswordProtectController;
use Statamic\Facades\OAuth;
use Statamic\Facades\TwoFactor;
use Statamic\Http\Controllers\ActivateAccountController;
use Statamic\Http\Controllers\Auth\ElevatedSessionController;
use Statamic\Http\Controllers\ForgotPasswordController;
use Statamic\Http\Controllers\FormController;
use Statamic\Http\Controllers\FrontendController;
use Statamic\Http\Controllers\OAuthController;
use Statamic\Http\Controllers\PhoneHomeController;
use Statamic\Http\Controllers\ResetPasswordController;
use Statamic\Http\Controllers\TwoFactorChallengeController;
use Statamic\Http\Controllers\TwoFactorSetupController;
use Statamic\Http\Controllers\User\LoginController;
use Statamic\Http\Controllers\User\PasskeyController;
use Statamic\Http\Controllers\User\PasskeyLoginController;
use Statamic\Http\Controllers\User\PasswordController;
use Statamic\Http\Controllers\User\ProfileController;
use Statamic\Http\Controllers\User\RegisterController;
use Statamic\Http\Controllers\User\TwoFactorAuthenticationController;
use Statamic\Http\Controllers\User\TwoFactorRecoveryCodesController;
use Statamic\Http\Middleware\AuthGuard;
use Statamic\Http\Middleware\CP\AuthGuard as CPAuthGuard;
use Statamic\Http\Middleware\CP\HandleInertiaRequests;
use Statamic\Http\Middleware\RedirectIfTwoFactorSetupIncomplete;
use Statamic\Http\Middleware\RequireElevatedSession;
use Statamic\Statamic;
use Statamic\StaticCaching\NoCache\CsrfTokenController;
use Statamic\StaticCaching\NoCache\NoCacheController;
use Statamic\StaticCaching\NoCache\NoCacheLocalize;

Route::name('statamic.')->group(function () {
    Route::group(['prefix' => config('statamic.routes.action')], function () {
        Route::post('forms/{form}', [FormController::class, 'submit'])->middleware([HandlePrecognitiveRequests::class, 'throttle:statamic.forms'])->name('forms.submit');

        Route::get('protect/password', [PasswordProtectController::class, 'show'])->name('protect.password.show')->middleware([HandleInertiaRequests::class]);
        Route::post('protect/password', [PasswordProtectController::class, 'store'])->name('protect.password.store');

        Route::group(['prefix' => 'auth', 'middleware' => [AuthGuard::class]], function () {
            Route::get('logout', [LoginController::class, 'logout'])->name('logout');

            Route::group(['middleware' => [HandlePrecognitiveRequests::class, 'throttle:statamic.auth']], function () {
                Route::post('login', [LoginController::class, 'login'])->name('login');
                Route::post('register', RegisterController::class)->name('register');
                Route::post('profile', ProfileController::class)->name('profile');
                Route::post('password', PasswordController::class)->name('password');
            });

            Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->middleware('throttle:statamic.auth')->name('password.email');
            Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
            Route::post('password/reset', [ResetPasswordController::class, 'reset'])->middleware('throttle:statamic.auth')->name('password.reset.action');

            if (config('statamic.users.elevated_sessions_enabled')) {
                Route::middleware('auth')->group(function () {
                    Route::get('confirm-password', [ElevatedSessionController::class, 'showForm'])->name('elevated-session')->middleware([HandleInertiaRequests::class]);
                    Route::post('elevated-session', [ElevatedSessionController::class, 'confirm'])->name('elevated-session.confirm')->middleware('throttle:statamic.auth');
                    Route::get('elevated-session/passkey-options', [ElevatedSessionController::class, 'options'])->name('elevated-session.passkey-options')->middleware('throttle:statamic.passkeys');
                    Route::get('elevated-session/resend-code', [ElevatedSessionController::class, 'resendCode'])->name('elevated-session.resend-code')->middleware('throttle:send-elevated-session-code');
                });
            }

            Route::group(['prefix' => 'passkeys'], function () {
                Route::middleware('throttle:statamic.passkeys')->group(function () {
                    Route::get('options', [PasskeyLoginController::class, 'options'])->name('passkeys.options');
                    Route::post('auth', [PasskeyLoginController::class, 'login'])->name('passkeys.login');
                });

                Route::middleware('auth')->group(function () {
                    Route::get('create', [PasskeyController::class, 'create'])->name('passkeys.create');
                    Route::post('/', [PasskeyController::class, 'store'])->name('passkeys.store');
                    Route::delete('{id}', [PasskeyController::class, 'destroy'])->name('passkeys.destroy');
                });
            });

            if (TwoFactor::enabled()) {
                Route::get('two-factor-setup', TwoFactorSetupController::class)->name('two-factor-setup');
                Route::get('two-factor-challenge', [TwoFactorChallengeController::class, 'index'])->name('two-factor-challenge');
                Route::post('two-factor-challenge', [TwoFactorChallengeController::class, 'store']);

                Route::middleware(['auth', RequireElevatedSession::class])->withoutMiddleware(RedirectIfTwoFactorSetupIncomplete::class)->group(function () {
                    Route::post('two-factor/enable', [TwoFactorAuthenticationController::class, 'enable'])->name('users.two-factor.enable');
                    Route::post('two-factor/confirm', [TwoFactorAuthenticationController::class, 'confirm'])->name('users.two-factor.confirm');
                    Route::delete('two-factor/disable', [TwoFactorAuthenticationController::class, 'disable'])->name('users.two-factor.disable');
                    Route::get('two-factor/recovery-codes', [TwoFactorRecoveryCodesController::class, 'show'])->name('users.two-factor.recovery-codes.show');
                    Route::post('two-factor/recovery-codes', [TwoFactorRecoveryCodesController::class, 'store'])->name('users.two-factor.recovery-codes.generate');
                    Route::get('two-factor/recovery-codes/download', [TwoFactorRecoveryCodesController::class, 'download'])->name('users.two-factor.recovery-codes.download');
                });
            }
        });

        Route::group(['prefix' => 'auth', 'middleware' => [CPAuthGuard::class]], function () {
            Route::get('activate/{token}', [ActivateAccountController::class, 'showResetForm'])->name('account.activate');
            Route::post('activate', [ActivateAccountController::class, 'reset'])->name('account.activate.action');
        });

        Route::post('nocache', NoCacheController::class)
            ->middleware(NoCacheLocalize::class)
            ->withoutMiddleware(['App\Http\Middleware\VerifyCsrfToken', 'Illuminate\Foundation\Http\Middleware\VerifyCsrfToken', 'Illuminate\Foundation\Http\Middleware\PreventRequestForgery'])
            ->name('nocache');

        Route::post('csrf', CsrfTokenController::class)
            ->withoutMiddleware(['App\Http\Middleware\VerifyCsrfToken', 'Illuminate\Foundation\Http\Middleware\VerifyCsrfToken', 'Illuminate\Foundation\Http\Middleware\PreventRequestForgery']);

        Statamic::additionalActionRoutes();
    });

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
