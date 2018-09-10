<?php

use Statamic\Http\Middleware\CP\Authenticate;
use Statamic\Http\Middleware\CP\Configurable;

Route::group(['prefix' => 'auth'], function () {
    Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
    Route::post('login', 'Auth\LoginController@login');
    Route::get('logout', 'Auth\LoginController@logout')->name('logout');
    Route::get('/login.reset', function () { return ''; })->name('login.reset'); // TODO
});

Route::group([
    'middleware' => [Authenticate::class, 'can:access cp']
], function () {
    Statamic::additionalCpRoutes();

    Route::redirect('/', 'cp/dashboard')->name('index');
    Route::get('dashboard', 'DashboardController@index')->name('dashboard');

    Route::resource('structures', 'StructuresController');

    Route::resource('collections', 'CollectionsController');
    Route::resource('collections.entries', 'EntriesController', ['except' => 'show']);

    Route::resource('asset-containers', 'AssetContainersController');
    Route::get('assets/browse', 'AssetBrowserController@index')->name('assets.browse.index');
    Route::get('assets/browse/folders/{container}/{path?}', 'AssetBrowserController@folder')->where('path', '.*');
    Route::get('assets/browse/{container}/{path?}', 'AssetBrowserController@show')->where('path', '.*')->name('assets.browse.show');
    Route::get('assets-fieldtype', 'AssetsFieldtypeController@index');
    Route::resource('assets', 'AssetsController');
    Route::get('assets/{asset}/download', 'AssetsController@download')->name('assets.download');
    Route::get('thumbnails/{asset}/{size?}', 'AssetThumbnailController@show')->name('assets.thumbnails.show');
});

Route::view('/playground', 'statamic::playground')->name('playground');

// Just to make stuff work.
Route::get('/account', function () { return ''; })->name('account');
Route::get('/search', function () { return ''; })->name('search.global');
Route::get('/account/password', function () { return ''; })->name('account.password');

Route::get('{segments}', 'CpController@pageNotFound')->where('segments', '.*')->name('404');
