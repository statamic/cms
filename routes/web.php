<?php

/**
 * Glide
 * On-the-fly URL-based image transforms.
 */
Route::group(['prefix' => Config::get('assets.image_manipulation_route')], function () {
    Route::get('/asset/{container}/{path?}', 'GlideController@generateByAsset')->where('path', '.*');
    Route::get('/http/{url}/{filename?}', 'GlideController@generateByUrl');
    Route::get('{path}', 'GlideController@generateByPath')->where('path', '.*');
});

/**
 * Front-end
 * All front-end website requests go through a single controller method.
 */
Route::any('/{segments?}', 'FrontendController@index')->where('segments', '.*')->name('site')->middleware(['staticcache']);
