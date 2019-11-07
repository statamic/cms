<?php

use Statamic\Facades\Utility;

Route::group(['prefix' => 'auth', 'namespace' => 'Auth'], function () {
    Route::get('login', 'LoginController@showLoginForm')->name('login');
    Route::post('login', 'LoginController@login');
    Route::get('logout', 'LoginController@logout')->name('logout');

    Route::get('password/reset', 'ForgotPasswordController@showLinkRequestForm')->name('password.request');
    Route::post('password/email', 'ForgotPasswordController@sendResetLinkEmail')->name('password.email');

    Route::get('token', 'CsrfTokenController')->name('token');
    Route::get('extend', 'ExtendSessionController')->name('extend');

    Route::get('unauthorized', 'UnauthorizedController')->name('unauthorized');
});

Route::group([
    'middleware' => Statamic::cpMiddleware()
], function () {
    Statamic::additionalCpRoutes();

    Route::get('/', 'StartPageController')->name('index');
    Route::get('dashboard', 'DashboardController@index')->name('dashboard');

    Route::get('select-site/{handle}', 'SelectSiteController@select');

    Route::group(['namespace' => 'Structures'], function () {
        Route::resource('structures', 'StructuresController');
        Route::resource('structures.pages', 'StructurePagesController', ['only' => ['index', 'store']]);
    });

    Route::get('structures/{collection}/entries/{entry}/{slug}', 'Collections\EntriesController@edit')->name('structures.entries.edit');

    Route::group(['namespace' => 'Collections'], function () {
        Route::resource('collections', 'CollectionsController');

        Route::group(['prefix' => 'collections/{collection}/entries'], function () {
            Route::get('/', 'EntriesController@index')->name('collections.entries.index');
            Route::post('actions', 'EntryActionController')->name('collections.entries.actions');
            Route::get('create/{site}', 'EntriesController@create')->name('collections.entries.create');
            Route::post('create/{site}/preview', 'EntryPreviewController@create')->name('collections.entries.preview.create');
            Route::post('reorder', 'ReorderEntriesController')->name('collections.entries.reorder');
            Route::post('{site}', 'EntriesController@store')->name('collections.entries.store');

            Route::group(['prefix' => '{entry}/{slug}'], function () {
                Route::get('/', 'EntriesController@edit')->name('collections.entries.edit');
                Route::post('publish', 'PublishedEntriesController@store')->name('collections.entries.published.store');
                Route::post('unpublish', 'PublishedEntriesController@destroy')->name('collections.entries.published.destroy');
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

        Route::group(['prefix' => 'taxonomies/{taxonomy}/terms'], function () {
            Route::get('/', 'TermsController@index')->name('taxonomies.terms.index');
            Route::post('actions', 'TermActionController')->name('taxonomies.terms.actions');
            Route::get('create/{site}', 'TermsController@create')->name('taxonomies.terms.create');
            Route::post('{site}', 'TermsController@store')->name('taxonomies.terms.store');

            Route::group(['prefix' => '{term}/{site?}'], function () {
                Route::get('/', 'TermsController@edit')->name('taxonomies.terms.edit');
                Route::post('/', 'PublishedTermsController@store')->name('taxonomies.terms.published.store');
                Route::delete('/', 'PublishedTermsController@destroy')->name('taxonomies.terms.published.destroy');

                Route::resource('revisions', 'TermRevisionsController', [
                    'as' => 'taxonomies.terms',
                    'only' => ['index', 'store', 'show'],
                ]);

                Route::post('restore-revision', 'RestoreTermRevisionController')->name('taxonomies.terms.restore-revision');
                Route::patch('/', 'TermsController@update')->name('taxonomies.terms.update');
            });
        });
    });

    Route::get('globals', 'GlobalsController@index')->name('globals.index');
    Route::get('globals/create', 'GlobalsController@create')->name('globals.create');
    Route::post('globals', 'GlobalsController@store')->name('globals.store');
    Route::patch('globals/{global}/meta', 'GlobalsController@updateMeta')->name('globals.update-meta');
    Route::delete('globals/{id}', 'GlobalsController@destroy')->name('globals.destroy');
    Route::get('globals/{id}/{handle}', 'GlobalsController@edit')->name('globals.edit');
    Route::patch('globals/{id}/{handle}', 'GlobalsController@update')->name('globals.update');
    Route::post('globals/{id}/{handle}/localize', 'Globals\LocalizeGlobalsController')->name('globals.localize');

    Route::group(['namespace' => 'Assets'], function () {
        Route::resource('asset-containers', 'AssetContainersController');
        Route::post('asset-containers/{container}/folders', 'FoldersController@store');
        Route::patch('asset-containers/{container}/folders/{path}', 'FoldersController@update')->where('path', '.*');
        Route::post('assets/actions', 'ActionController')->name('assets.actions');
        Route::get('assets/browse', 'BrowserController@index')->name('assets.browse.index');
        Route::get('assets/browse/search/{container}', 'BrowserController@search');
        Route::post('assets/browse/folders/{container}/actions', 'FolderActionController')->name('assets.folders.actions');
        Route::get('assets/browse/folders/{container}/{path?}', 'BrowserController@folder')->where('path', '.*');
        Route::get('assets/browse/{container}/{path?}/edit', 'BrowserController@edit')->where('path', '.*')->name('assets.browse.edit');
        Route::get('assets/browse/{container}/{path?}', 'BrowserController@show')->where('path', '.*')->name('assets.browse.show');
        Route::get('assets-fieldtype', 'FieldtypeController@index');
        Route::resource('assets', 'AssetsController');
        Route::get('assets/{asset}/download', 'AssetsController@download')->name('assets.download');
        Route::get('thumbnails/{asset}/{size?}', 'ThumbnailController@show')->name('assets.thumbnails.show');
    });

    Route::group(['prefix' => 'fields', 'namespace' => 'Fields'], function () {
        Route::get('/', 'FieldsController@index')->name('fields.index');
        Route::post('edit', 'FieldsController@edit')->name('fields.edit');
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
        Route::get('updater/{product}', 'UpdateProductController@show')->name('updater.product');
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
        Route::post('users/actions', 'UserActionController')->name('users.actions');
        Route::resource('users', 'UsersController');
        Route::patch('users/{user}/password', 'PasswordController@update')->name('users.password.update');
        Route::get('account', 'AccountController')->name('account');
        Route::resource('user-groups', 'UserGroupsController');
        Route::resource('roles', 'RolesController');
        Route::resource('preferences', 'PreferenceController');
    });

    Route::post('user-exists', 'Users\UserWizardController')->name('user.exists');

    Route::get('search', 'SearchController')->name('search');

    Route::get('utilities', 'Utilities\UtilitiesController@index')->name('utilities.index');
    Utility::routes();

    Route::group(['prefix' => 'fieldtypes', 'namespace' => 'Fieldtypes'], function () {
        Route::get('relationship', 'RelationshipFieldtypeController@index')->name('relationship.index');
        Route::get('relationship/data', 'RelationshipFieldtypeController@data')->name('relationship.data');
    });

    Route::group(['prefix' => 'api', 'as' => 'api', 'namespace' => 'API'], function () {
        Route::resource('addons', 'AddonsController');
        Route::resource('templates', 'TemplatesController');
    });

    Route::get('session-timeout', 'SessionTimeoutController')->name('session.timeout');

    Route::view('/playground', 'statamic::playground')->name('playground');

    Route::get('{segments}', 'CpController@pageNotFound')->where('segments', '.*')->name('404');
});