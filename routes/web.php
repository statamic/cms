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
});

/**
 * Front-end
 * All front-end website requests go through a single controller method.
 */
Route::any('/{segments?}', 'FrontendController@index')->where('segments', '.*')->name('site')
     ->middleware(\Statamic\StaticCaching\Middleware\Cache::class);
