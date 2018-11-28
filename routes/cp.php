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

    Route::get('/', function () {
        session()->reflash();
        return redirect()->route('statamic.cp.dashboard');
    })->name('index');

    Route::get('dashboard', 'DashboardController@index')->name('dashboard');

    // Structures
    Route::resource('structures', 'StructuresController');

    // Collections
    Route::resource('collections', 'CollectionsController');
    Route::resource('collections.entries', 'EntriesController', ['except' => 'show']);

    // Collections
    Route::resource('globals', 'GlobalsController');
    Route::patch('globals/{global}/meta', 'GlobalsController@updateMeta')->name('globals.update-meta');

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
    Route::post('fieldsets/quick', 'FieldsetController@quickStore');
    Route::post('fieldsets/{fieldset}/fields', 'FieldsetFieldController@store');
    Route::resource('blueprints', 'BlueprintController');
    Route::get('fieldtypes', 'FieldtypesController@index');
    Route::get('publish-blueprints/{blueprint}', 'PublishBlueprintController@show');

    // Composer
    Route::get('composer/check', 'ComposerOutputController@check');

    // Updater
    Route::get('updater', 'UpdaterController@index')->name('updater.index');
    Route::get('updater/count', 'UpdaterController@count');
    Route::get('updater/{product}', 'UpdateProductController@index')->name('updater.product.index');
    Route::get('updater/{product}/changelog', 'UpdateProductController@changelog');
    Route::post('updater/{product}/update', 'UpdateProductController@update');
    Route::post('updater/{product}/update-to-latest', 'UpdateProductController@updateToLatest');
    Route::post('updater/{product}/install-explicit-version', 'UpdateProductController@installExplicitVersion');

    // Addons
    Route::get('addons', 'AddonsController@index')->name('addons.index');
    Route::post('addons/install', 'AddonsController@install');
    Route::post('addons/uninstall', 'AddonsController@uninstall');

    // Forms
    Route::resource('forms', 'FormsController');
    Route::resource('forms.submissions', 'FormSubmissionsController');
    Route::get('forms/{form}/export/{type}', 'FormExportController@export')->name('forms.export');

    // Users
    Route::resource('users', 'UsersController');
    Route::get('account', 'AccountController')->name('account');
    Route::resource('user-groups', 'UserGroupsController');
    Route::resource('roles', 'RolesController');

    // Tools
    Route::get('phpinfo', 'PhpInfoController')->name('phpinfo');

    Route::get('suggestions/{type}', 'SuggestionController@show');

    // Local API
    Route::group(['prefix' => 'api', 'as' => 'api', 'namespace' => 'Api'], function () {
        Route::resource('addons', 'AddonsController');
    });
});

Route::view('/playground', 'statamic::playground')->name('playground');

// Just to make stuff work.
Route::get('/search', function () { return ''; })->name('search.global');
Route::get('/account/password', function () { return ''; })->name('account.password');

Route::get('{segments}', 'CpController@pageNotFound')->where('segments', '.*')->name('404');
