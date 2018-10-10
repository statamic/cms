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

    // Structures
    Route::resource('structures', 'StructuresController');

    // Collections
    Route::resource('collections', 'CollectionsController');
    Route::resource('collections.entries', 'EntriesController', ['except' => 'show']);

    // Assets
    Route::resource('asset-containers', 'AssetContainersController');
    Route::get('assets/browse', 'AssetBrowserController@index')->name('assets.browse.index');
    Route::get('assets/browse/folders/{container}/{path?}', 'AssetBrowserController@folder')->where('path', '.*');
    Route::get('assets/browse/{container}/{path?}', 'AssetBrowserController@show')->where('path', '.*')->name('assets.browse.show');
    Route::get('assets-fieldtype', 'AssetsFieldtypeController@index');
    Route::resource('assets', 'AssetsController');
    Route::get('assets/{asset}/download', 'AssetsController@download')->name('assets.download');
    Route::get('thumbnails/{asset}/{size?}', 'AssetThumbnailController@show')->name('assets.thumbnails.show');

    // Fields
    Route::resource('fieldsets', 'FieldsetController');
    Route::resource('blueprints', 'BlueprintController');
    Route::get('fieldtypes', 'FieldtypesController@index');
    Route::get('publish-fieldsets/{fieldset}', 'PublishFieldsetController@show');

    // Composer
    Route::get('composer/check', 'ComposerOutputController@check');

    // Updater
    Route::get('updater', 'UpdaterController@index')->name('updater.index');
    Route::get('updater/changelog', 'UpdaterController@changelog');
    Route::get('updater/count', 'UpdaterController@count');
    Route::post('updater/update', 'UpdaterController@update');
    Route::post('updater/update-to-latest', 'UpdaterController@updateToLatest');
    Route::post('updater/install-explicit-version', 'UpdaterController@installExplicitVersion');

    // Addons
    Route::get('addons', 'AddonsController@index')->name('addons.index');
    Route::get('addons/installed', 'AddonsController@installed');
    Route::post('addons/install', 'AddonsController@install');
    Route::post('addons/uninstall', 'AddonsController@uninstall');
});

Route::view('/playground', 'statamic::playground')->name('playground');

// Just to make stuff work.
Route::get('/account', function () { return ''; })->name('account');
Route::get('/search', function () { return ''; })->name('search.global');
Route::get('/account/password', function () { return ''; })->name('account.password');

Route::get('{segments}', 'CpController@pageNotFound')->where('segments', '.*')->name('404');
