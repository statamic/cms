<?php

use Statamic\Http\Middleware\CP\Authenticate;
use Statamic\Http\Middleware\CP\Configurable;

Route::group(['prefix' => 'auth'], function () {
    Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
    Route::post('login', 'Auth\LoginController@login');
    Route::get('logout', 'Auth\LoginController@logout')->name('logout');

    Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
    Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');

    Route::get('token', 'Auth\CsrfTokenController')->name('token');
    Route::get('extend', 'Auth\ExtendSessionController')->name('extend');
});

Route::group([
    'middleware' => [Authenticate::class, 'can:access cp']
], function () {
    Statamic::additionalCpRoutes();

    Route::get('/', 'StartPageController')->name('index');
    Route::get('dashboard', 'DashboardController@index')->name('dashboard');

    // Structures
    Route::resource('structures', 'StructuresController');
    Route::resource('structures.pages', 'StructurePagesController', ['only' => ['index', 'store']]);

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
    Route::patch('users/{user}/password', 'UserPasswordController@update')->name('users.password.update');
    Route::get('account', 'AccountController')->name('account');
    Route::resource('user-groups', 'UserGroupsController');
    Route::resource('roles', 'RolesController');

    // Search
    Route::get('search', 'SearchController')->name('search');

    // Utilities
    Route::get('utilities/phpinfo', 'PhpInfoController')->name('utilities.phpinfo');
    Route::get('utilities/clear-cache', 'ClearCacheController@index')->name('utilities.clear-cache.index');
    Route::post('utilities/clear-cache', 'ClearCacheController@clear')->name('utilities.clear-cache.clear');
    Route::get('utilities/search', 'UpdateSearchController@index')->name('utilities.search');
    Route::post('utilities/search', 'UpdateSearchController@update');

    Route::get('suggestions/{type}', 'SuggestionController@show');

    // Fieldtypes
    Route::group(['prefix' => 'fieldtypes', 'namespace' => 'Fieldtypes'], function () {
        Route::get('relationship', 'RelationshipFieldtypeController@index');
        Route::get('relationship/data', 'RelationshipFieldtypeController@data');
        Route::get('collections', 'CollectionsFieldtypeController@index');
        Route::get('collections/data', 'CollectionsFieldtypeController@data');
        Route::get('form', 'FormFieldtypeController@index');
        Route::get('form/data', 'FormFieldtypeController@data');
        Route::get('blueprints', 'BlueprintsFieldtypeController@index');
        Route::get('blueprints/data', 'BlueprintsFieldtypeController@data');
    });

    Route::get('session-timeout', 'SessionTimeoutController')->name('session.timeout');

    // Local API
    Route::group(['prefix' => 'api', 'as' => 'api', 'namespace' => 'Api'], function () {
        Route::resource('addons', 'AddonsController');
        Route::resource('users', 'UsersController');
    });
});

Route::view('/playground', 'statamic::playground')->name('playground');

Route::get('{segments}', 'CpController@pageNotFound')->where('segments', '.*')->name('404');
