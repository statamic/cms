<?php

use Illuminate\Support\Facades\Route;
use Statamic\Facades\OAuth;
use Statamic\Facades\Site;
use Statamic\Facades\URL;
use Statamic\Statamic;

Route::name('statamic.')->group(function () {
    /**
     * Glide
     * On-the-fly URL-based image transforms.
     */
    if (! config('statamic.assets.image_manipulation.cache')) {
        Site::all()->map(function ($site) {
            return URL::makeRelative($site->url());
        })->unique()->each(function ($sitePrefix) {
            Route::group(['prefix' => $sitePrefix.'/'.config('statamic.assets.image_manipulation.route')], function () {
                Route::get('/asset/{container}/{path?}', 'GlideController@generateByAsset')->where('path', '.*');
                Route::get('/http/{url}/{filename?}', 'GlideController@generateByUrl');
                Route::get('{path}', 'GlideController@generateByPath')->where('path', '.*');
            });
        });
    }

    Route::group(['prefix' => config('statamic.routes.action')], function () {
        Route::post('forms/{form}', 'FormController@submit')->name('forms.submit');

        Route::get('protect/password', '\Statamic\Auth\Protect\Protectors\Password\Controller@show')->name('protect.password.show');
        Route::post('protect/password', '\Statamic\Auth\Protect\Protectors\Password\Controller@store')->name('protect.password.store');

        Route::group(['prefix' => 'auth'], function () {
            Route::post('login', 'UserController@login')->name('login');
            Route::get('logout', 'UserController@logout')->name('logout');
            Route::post('register', 'UserController@register')->name('register');

            Route::post('password/email', 'ForgotPasswordController@sendResetLinkEmail')->name('password.email');
            Route::get('password/reset/{token}', 'ResetPasswordController@showResetForm')->name('password.reset');
            Route::post('password/reset', 'ResetPasswordController@reset')->name('password.reset.action');

            Route::get('activate/{token}', 'ActivateAccountController@showResetForm')->name('account.activate');
            Route::post('activate', 'ActivateAccountController@reset')->name('account.activate.action');
        });

        Statamic::additionalActionRoutes();
    });

    if (OAuth::enabled()) {
        Route::get(config('statamic.oauth.routes.login'), 'OAuthController@redirectToProvider')->name('oauth.login');
        Route::get(config('statamic.oauth.routes.callback'), 'OAuthController@handleProviderCallback')->name('oauth.callback');
    }

    Route::post('graphql', 'GraphQLController@index')->name('graphql');
});

Statamic::additionalWebRoutes();

/**
 * Front-end
 * All front-end website requests go through a single controller method.
 */
Route::any('/{segments?}', 'FrontendController@index')
    ->where('segments', '.*')
    ->name('statamic.site');
