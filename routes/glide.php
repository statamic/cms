<?php

use Illuminate\Support\Facades\Route;
use Statamic\Facades\Glide;
use Statamic\Facades\Site;
use Statamic\Facades\URL;

Site::all()->map(function ($site) {
    return URL::makeRelative($site->url());
})->unique()->each(function ($sitePrefix) {
    Route::group(['prefix' => $sitePrefix.'/'.Glide::route()], function () {
        Route::get('/asset/{container}/{path?}', 'GlideController@generateByAsset')->where('path', '.*');
        Route::get('/http/{url}/{filename?}', 'GlideController@generateByUrl');
        Route::get('{path}', 'GlideController@generateByPath')->where('path', '.*');
    });
});
