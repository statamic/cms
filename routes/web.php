<?php

/**
 * Glide
 * On-the-fly URL-based image transforms.
 */
Route::group(['prefix' => Config::get('statamic.assets.image_manipulation.route')], function () {
    Route::get('/asset/{container}/{path?}', 'GlideController@generateByAsset')->where('path', '.*');
    Route::get('/http/{url}/{filename?}', 'GlideController@generateByUrl');
    Route::get('{path}', 'GlideController@generateByPath')->where('path', '.*');
});

Route::group(['prefix' => config('statamic.routes.action')], function () {
    Route::post('form/create', 'FormController@create');

    Route::group(['prefix' => 'user'], function () {
        Route::post('login', 'UserController@login');
        Route::get('logout', 'UserController@logout');
        Route::post('register', 'UserController@register');
        Route::get('reset', 'UserController@reset');
        Route::post('reset', 'UserController@reset');
        Route::post('forgot', 'UserController@forgot');
    });
});

/**
 * Front-end
 * All front-end website requests go through a single controller method.
 */
Route::any('/{segments?}', 'FrontendController@index')->where('segments', '.*')->name('site')
     ->middleware(\Statamic\StaticCaching\Middleware\Cache::class);
