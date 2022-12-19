<?php

use Illuminate\Support\Facades\Route;
use Statamic\Facades\OAuth;
use Statamic\Statamic;

Route::name('statamic.')->group(function () {
    Route::group(['prefix' => config('statamic.routes.action')], function () {
        Route::post('forms/{form}', 'FormController@submit')->name('forms.submit');

        Route::get('protect/password', '\Statamic\Auth\Protect\Protectors\Password\Controller@show')->name('protect.password.show');
        Route::post('protect/password', '\Statamic\Auth\Protect\Protectors\Password\Controller@store')->name('protect.password.store');

        Route::group(['prefix' => 'auth', 'middleware' => [\Statamic\Http\Middleware\AuthGuard::class]], function () {
            Route::post('login', 'UserController@login')->name('login');
            Route::get('logout', 'UserController@logout')->name('logout');
            Route::post('register', 'UserController@register')->name('register');
            Route::post('profile', 'UserController@profile')->name('profile');
            Route::post('password', 'UserController@password')->name('password');

            Route::post('password/email', 'ForgotPasswordController@sendResetLinkEmail')->name('password.email');
            Route::get('password/reset/{token}', 'ResetPasswordController@showResetForm')->name('password.reset');
            Route::post('password/reset', 'ResetPasswordController@reset')->name('password.reset.action');
        });

        Route::group(['prefix' => 'auth', 'middleware' => [\Statamic\Http\Middleware\CP\AuthGuard::class]], function () {
            Route::get('activate/{token}', 'ActivateAccountController@showResetForm')->name('account.activate');
            Route::post('activate', 'ActivateAccountController@reset')->name('account.activate.action');
        });

        Statamic::additionalActionRoutes();
    });

    Route::prefix(config('statamic.routes.action'))
        ->post('nocache', '\Statamic\StaticCaching\NoCache\Controller')
        ->withoutMiddleware('App\Http\Middleware\VerifyCsrfToken');

    if (OAuth::enabled()) {
        Route::get(config('statamic.oauth.routes.login'), 'OAuthController@redirectToProvider')->name('oauth.login');
        Route::get(config('statamic.oauth.routes.callback'), 'OAuthController@handleProviderCallback')->name('oauth.callback');
    }
});

if (config('statamic.routes.enabled')) {
    Statamic::additionalWebRoutes();

    /*
     * Front-end
     * All front-end website requests go through a single controller method.
     */
    Route::any('/{segments?}', 'FrontendController@index')
        ->where('segments', Statamic::frontendRouteSegmentRegex())
        ->name('statamic.site');
}
