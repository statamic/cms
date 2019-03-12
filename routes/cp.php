<?php

use Statamic\Http\Middleware\CP\Authenticate;
use Statamic\Http\Middleware\CP\Configurable;

Route::group(['prefix' => 'auth', 'namespace' => 'Auth'], function () {
    Route::get('login', 'LoginController@showLoginForm')->name('login');
    Route::post('login', 'LoginController@login');
    Route::get('logout', 'LoginController@logout')->name('logout');

    Route::get('password/reset', 'ForgotPasswordController@showLinkRequestForm')->name('password.request');
    Route::post('password/email', 'ForgotPasswordController@sendResetLinkEmail')->name('password.email');

    Route::get('token', 'CsrfTokenController')->name('token');
    Route::get('extend', 'ExtendSessionController')->name('extend');
});

Route::group([
    'middleware' => [Authenticate::class, 'can:access cp']
], function () {
    Statamic::additionalCpRoutes();

    Route::get('/', 'StartPageController')->name('index');
    Route::get('dashboard', 'DashboardController@index')->name('dashboard');

    Route::get('select-site/{handle}', 'SelectSiteController@select');

    Route::group(['namespace' => 'Structures'], function () {
        Route::resource('structures', 'StructuresController');
        Route::resource('structures.pages', 'StructurePagesController', ['only' => ['index', 'store']]);
    });

    Route::group(['namespace' => 'Collections'], function () {
        Route::resource('collections', 'CollectionsController');
        Route::get('collections/{collection}/entries', 'EntriesController@index')->name('collections.entries.index');
        Route::post('collections/{collection}/entries/action', 'EntryActionController')->name('collections.entries.action');
        Route::post('collections/{collection}/entries/columns', 'EntryColumnController')->name('collections.entries.columns');
        Route::get('collections/{collection}/entries/create/{site}', 'EntriesController@create')->name('collections.entries.create');
        Route::post('collections/{collection}/entries/{site}', 'EntriesController@store')->name('collections.entries.store');
        Route::get('collections/{collection}/entries/{id}/{slug}/{site}', 'EntriesController@edit')->name('collections.entries.edit');
        Route::post('collections/{collection}/entries/{id}/{slug}/{site}/preview', 'EntryPreviewController@edit')->name('collections.entries.preview.edit');
        Route::post('collections/{collection}/entries/create/{site}/preview', 'EntryPreviewController@create')->name('collections.entries.preview.create');
        Route::patch('collections/{collection}/entries/{id}/{slug}/{site}', 'EntriesController@update')->name('collections.entries.update');
    });

    Route::get('globals', 'GlobalsController@index')->name('globals.index');
    Route::get('globals/create', 'GlobalsController@create')->name('globals.create');
    Route::post('globals', 'GlobalsController@store')->name('globals.store');
    Route::get('globals/{id}/{handle}/{site}', 'GlobalsController@edit')->name('globals.edit');
    Route::patch('globals/{id}/{handle}/{site}', 'GlobalsController@update')->name('globals.update');
    Route::patch('globals/{global}/meta', 'GlobalsController@updateMeta')->name('globals.update-meta');

    Route::group(['namespace' => 'Assets'], function () {
        Route::resource('asset-containers', 'AssetContainersController');
        Route::post('asset-containers/{container}/folders', 'FoldersController@store');
        Route::patch('asset-containers/{container}/folders/{path}', 'FoldersController@update')->where('path', '.*');
        Route::post('assets/action', 'ActionController')->name('assets.action');
        Route::get('assets/browse', 'BrowserController@index')->name('assets.browse.index');
        Route::get('assets/browse/folders/{container}/{path?}', 'BrowserController@folder')->where('path', '.*');
        Route::get('assets/browse/{container}/{path?}', 'BrowserController@show')->where('path', '.*')->name('assets.browse.show');
        Route::get('assets-fieldtype', 'FieldtypeController@index');
        Route::resource('assets', 'AssetsController');
        Route::get('assets/{asset}/download', 'AssetsController@download')->name('assets.download');
        Route::get('thumbnails/{asset}/{size?}', 'ThumbnailController@show')->name('assets.thumbnails.show');
    });

    Route::group(['namespace' => 'Fields'], function () {
        Route::resource('fieldsets', 'FieldsetController');
        Route::post('fieldsets/quick', 'FieldsetController@quickStore');
        Route::post('fieldsets/{fieldset}/fields', 'FieldsetFieldController@store');
        Route::resource('blueprints', 'BlueprintController');
        Route::get('fieldtypes', 'FieldtypesController@index');
        Route::get('publish-blueprints/{blueprint}', 'PublishBlueprintController@show');
    });

    Route::get('composer/check', 'ComposerOutputController@check');

    Route::group(['namespace' => 'Updater'], function () {
        Route::get('updater', 'UpdaterController@index')->name('updater.index');
        Route::get('updater/count', 'UpdaterController@count');
        Route::get('updater/{product}', 'UpdateProductController@index')->name('updater.products.index');
        Route::get('updater/{product}/changelog', 'UpdateProductController@changelog');
        Route::post('updater/{product}/update', 'UpdateProductController@update');
        Route::post('updater/{product}/update-to-latest', 'UpdateProductController@updateToLatest');
        Route::post('updater/{product}/install-explicit-version', 'UpdateProductController@installExplicitVersion');
    });

    Route::get('addons', 'AddonsController@index')->name('addons.index');
    Route::post('addons/install', 'AddonsController@install');
    Route::post('addons/uninstall', 'AddonsController@uninstall');

    Route::group(['namespace' => 'Forms'], function () {
        Route::resource('forms', 'FormsController');
        Route::resource('forms.submissions', 'FormSubmissionsController');
        Route::get('forms/{form}/export/{type}', 'FormExportController@export')->name('forms.export');
    });

    Route::group(['namespace' => 'Users'], function () {
        Route::post('users/action', 'UserActionController')->name('users.action');
        Route::resource('users', 'UsersController');
        Route::patch('users/{user}/password', 'PasswordController@update')->name('users.password.update');
        Route::get('account', 'AccountController')->name('account');
        Route::resource('user-groups', 'UserGroupsController');
        Route::resource('roles', 'RolesController');
        Route::resource('preferences', 'PreferenceController');
    });

    Route::get('search', 'SearchController')->name('search');

    Route::group(['namespace' => 'Utilities'], function () {
        Route::get('utilities/phpinfo', 'PhpInfoController')->name('utilities.phpinfo');
        Route::get('utilities/cache', 'CacheController@index')->name('utilities.cache.index');
        Route::post('utilities/cache/{cache}', 'CacheController@clear')->name('utilities.cache.clear');
        Route::get('utilities/search', 'UpdateSearchController@index')->name('utilities.search');
        Route::post('utilities/search', 'UpdateSearchController@update');
    });

    Route::group(['prefix' => 'fieldtypes', 'namespace' => 'Fieldtypes'], function () {
        Route::get('relationship', 'RelationshipFieldtypeController@index');
        Route::get('relationship/data', 'RelationshipFieldtypeController@data');
        Route::get('collections', 'CollectionsFieldtypeController@index');
        Route::get('collections/data', 'CollectionsFieldtypeController@data');
    });

    Route::group(['prefix' => 'api', 'as' => 'api', 'namespace' => 'API'], function () {
        Route::resource('addons', 'AddonsController');
        Route::resource('templates', 'TemplatesController');
    });

    Route::get('session-timeout', 'SessionTimeoutController')->name('session.timeout');
});

Route::view('/playground', 'statamic::playground')->name('playground');

Route::get('{segments}', 'CpController@pageNotFound')->where('segments', '.*')->name('404');
