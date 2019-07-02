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

        Route::group(['prefix' => 'collections/{collection}/entries'], function () {
            Route::get('/', 'EntriesController@index')->name('collections.entries.index');
            Route::get('actions', 'EntryActionController@index')->name('collections.entries.actions');
            Route::post('actions', 'EntryActionController@run');
            Route::get('create/{site}', 'EntriesController@create')->name('collections.entries.create');
            Route::post('create/{site}/preview', 'EntryPreviewController@create')->name('collections.entries.preview.create');
            Route::post('reorder', 'ReorderEntriesController')->name('collections.entries.reorder');
            Route::post('{site}', 'EntriesController@store')->name('collections.entries.store');

            Route::group(['prefix' => '{entry}/{slug}'], function () {
                Route::get('/', 'EntriesController@edit')->name('collections.entries.edit');
                Route::post('/', 'PublishedEntriesController@store')->name('collections.entries.published.store');
                Route::delete('/', 'PublishedEntriesController@destroy')->name('collections.entries.published.destroy');
                Route::post('localize', 'LocalizeEntryController')->name('collections.entries.localize');

                Route::resource('revisions', 'EntryRevisionsController', [
                    'as' => 'collections.entries',
                    'only' => ['index', 'store', 'show'],
                ]);

                Route::post('restore-revision', 'RestoreEntryRevisionController')->name('collections.entries.restore-revision');
                Route::post('preview', 'EntryPreviewController@edit')->name('collections.entries.preview.edit');
                Route::get('preview', 'EntryPreviewController@show')->name('collections.entries.preview.popout');
                Route::patch('/', 'EntriesController@update')->name('collections.entries.update');
            });
        });
    });

    Route::group(['namespace' => 'Taxonomies'], function () {
        Route::resource('taxonomies', 'TaxonomiesController');
    });

    Route::get('globals', 'GlobalsController@index')->name('globals.index');
    Route::get('globals/create', 'GlobalsController@create')->name('globals.create');
    Route::post('globals', 'GlobalsController@store')->name('globals.store');
    Route::patch('globals/{global}/meta', 'GlobalsController@updateMeta')->name('globals.update-meta');
    Route::get('globals/{id}/{handle}', 'GlobalsController@edit')->name('globals.edit');
    Route::patch('globals/{id}/{handle}', 'GlobalsController@update')->name('globals.update');
    Route::post('globals/{id}/{handle}/localize', 'Globals\LocalizeGlobalsController')->name('globals.localize');

    Route::group(['namespace' => 'Assets'], function () {
        Route::resource('asset-containers', 'AssetContainersController');
        Route::post('asset-containers/{container}/folders', 'FoldersController@store');
        Route::patch('asset-containers/{container}/folders/{path}', 'FoldersController@update')->where('path', '.*');
        Route::get('assets/actions', 'ActionController@index')->name('assets.actions');
        Route::post('assets/actions', 'ActionController@run');
        Route::get('assets/browse', 'BrowserController@index')->name('assets.browse.index');
        Route::get('assets/browse/search/{container}', 'BrowserController@search');
        Route::get('assets/browse/folders/{container}/actions', 'FolderActionController@index')->name('assets.folders.actions');
        Route::post('assets/browse/folders/{container}/actions', 'FolderActionController@run');
        Route::get('assets/browse/folders/{container}/{path?}', 'BrowserController@folder')->where('path', '.*');
        Route::get('assets/browse/{container}/{path?}', 'BrowserController@show')->where('path', '.*')->name('assets.browse.show');
        Route::get('assets-fieldtype', 'FieldtypeController@index');
        Route::resource('assets', 'AssetsController');
        Route::get('assets/{asset}/download', 'AssetsController@download')->name('assets.download');
        Route::get('thumbnails/{asset}/{size?}', 'ThumbnailController@show')->name('assets.thumbnails.show');
    });

    Route::group(['prefix' => 'fields', 'namespace' => 'Fields'], function () {
        Route::get('/', 'FieldsController@index')->name('fields.index');
        Route::get('create', 'FieldsController@create')->name('fields.create');
        Route::get('show', 'FieldsController@show')->name('fields.show');
        Route::get('edit', 'FieldsController@edit')->name('fields.edit');
        Route::post('store', 'FieldsController@store')->name('fields.store');
        Route::post('update', 'FieldsController@update')->name('fields.update');
        Route::get('field-meta', 'MetaController@show');
        Route::resource('fieldsets', 'FieldsetController');
        Route::post('fieldsets/quick', 'FieldsetController@quickStore');
        Route::post('fieldsets/{fieldset}/fields', 'FieldsetFieldController@store');
        Route::resource('blueprints', 'BlueprintController');
        Route::get('fieldtypes', 'FieldtypesController@index');
        Route::get('publish-blueprints/{blueprint}', 'PublishBlueprintController@show');
    });

    Route::get('composer/check', 'ComposerOutputController@check');

    Route::group(['namespace' => 'Updater'], function () {
        Route::get('updater', 'UpdaterController@index')->name('updater');
        Route::get('updater/count', 'UpdaterController@count');
        Route::get('updater/{product}', 'UpdateProductController@index')->name('updater.product');
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
        Route::get('users/actions', 'UserActionController@index')->name('users.actions');
        Route::post('users/actions', 'UserActionController@run');
        Route::resource('users', 'UsersController');
        Route::patch('users/{user}/password', 'PasswordController@update')->name('users.password.update');
        Route::get('account', 'AccountController')->name('account');
        Route::resource('user-groups', 'UserGroupsController');
        Route::resource('roles', 'RolesController');
        Route::resource('preferences', 'PreferenceController');
    });

    Route::post('user-exists', 'Users\UserWizardController')->name('user.exists');

    Route::get('search', 'SearchController')->name('search');

    Route::group(['namespace' => 'Utilities'], function () {
        Route::get('utilities/phpinfo', 'PhpInfoController')->name('utilities.phpinfo');
        Route::get('utilities/cache', 'CacheController@index')->name('utilities.cache.index');
        Route::post('utilities/cache/{cache}', 'CacheController@clear')->name('utilities.cache.clear');
        Route::get('utilities/search', 'UpdateSearchController@index')->name('utilities.search');
        Route::post('utilities/search', 'UpdateSearchController@update');
    });

    Route::group(['prefix' => 'fieldtypes', 'namespace' => 'Fieldtypes'], function () {
        Route::get('relationship', 'RelationshipFieldtypeController@index')->name('relationship.index');
        Route::get('relationship/data', 'RelationshipFieldtypeController@data')->name('relationship.data');
    });

    Route::group(['prefix' => 'api', 'as' => 'api', 'namespace' => 'API'], function () {
        Route::resource('addons', 'AddonsController');
        Route::resource('templates', 'TemplatesController');
    });

    Route::get('session-timeout', 'SessionTimeoutController')->name('session.timeout');
});

Route::view('/playground', 'statamic::playground')->name('playground');

Route::get('{segments}', 'CpController@pageNotFound')->where('segments', '.*')->name('404');
