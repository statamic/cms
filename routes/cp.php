<?php

use Illuminate\Support\Facades\Route;
use Statamic\Facades\Utility;
use Statamic\Http\Middleware\RequireStatamicPro;
use Statamic\Statamic;

Route::group(['prefix' => 'auth', 'namespace' => 'Auth'], function () {
    Route::get('login', 'LoginController@showLoginForm')->name('login');
    Route::post('login', 'LoginController@login');
    Route::get('logout', 'LoginController@logout')->name('logout');

    Route::get('password/reset', 'ForgotPasswordController@showLinkRequestForm')->name('password.request');
    Route::post('password/email', 'ForgotPasswordController@sendResetLinkEmail')->name('password.email');
    Route::get('password/reset/{token}', 'ResetPasswordController@showResetForm')->name('password.reset');
    Route::post('password/reset', 'ResetPasswordController@reset')->name('password.reset.action');

    Route::get('token', 'CsrfTokenController')->name('token');
    Route::get('extend', 'ExtendSessionController')->name('extend');

    Route::get('unauthorized', 'UnauthorizedController')->name('unauthorized');
});

Route::middleware('statamic.cp.authenticated')->group(function () {
    Statamic::additionalCpRoutes();

    Route::get('/', 'StartPageController')->name('index');
    Route::get('dashboard', 'DashboardController@index')->name('dashboard');

    Route::get('select-site/{handle}', 'SelectSiteController@select');

    Route::group(['namespace' => 'Navigation'], function () {
        Route::resource('navigation', 'NavigationController');

        Route::get('navigation/{navigation}/blueprint', 'NavigationBlueprintController@edit')->name('navigation.blueprint.edit');
        Route::patch('navigation/{navigation}/blueprint', 'NavigationBlueprintController@update')->name('navigation.blueprint.update');
        Route::get('navigation/{navigation}/tree', 'NavigationTreeController@index')->name('navigation.tree.index');
        Route::patch('navigation/{navigation}/tree', 'NavigationTreeController@update')->name('navigation.tree.update');
        Route::post('navigation/{navigation}/pages', 'NavigationPagesController@update')->name('navigation.pages.update');
        Route::get('navigation/{navigation}/pages/create', 'NavigationPagesController@create')->name('navigation.pages.create');
        Route::get('navigation/{navigation}/pages/{edit}/edit', 'NavigationPagesController@edit')->name('navigation.pages.edit');
    });

    Route::group(['namespace' => 'Collections'], function () {
        Route::resource('collections', 'CollectionsController');
        Route::get('collections/{collection}/scaffold', 'ScaffoldCollectionController@index')->name('collections.scaffold');
        Route::post('collections/{collection}/scaffold', 'ScaffoldCollectionController@create')->name('collections.scaffold.create');
        Route::resource('collections.blueprints', 'CollectionBlueprintsController');
        Route::post('collections/{collection}/blueprints/reorder', 'ReorderCollectionBlueprintsController')->name('collections.blueprints.reorder');

        Route::get('collections/{collection}/tree', 'CollectionTreeController@index')->name('collections.tree.index');
        Route::patch('collections/{collection}/tree', 'CollectionTreeController@update')->name('collections.tree.update');

        Route::group(['prefix' => 'collections/{collection}/entries'], function () {
            Route::get('/', 'EntriesController@index')->name('collections.entries.index');
            Route::post('actions', 'EntryActionController@run')->name('collections.entries.actions.run');
            Route::post('actions/list', 'EntryActionController@bulkActions')->name('collections.entries.actions.bulk');
            Route::get('create/{site}', 'EntriesController@create')->name('collections.entries.create');
            Route::post('create/{site}/preview', 'EntryPreviewController@create')->name('collections.entries.preview.create');
            Route::post('reorder', 'ReorderEntriesController')->name('collections.entries.reorder');
            Route::post('{site}', 'EntriesController@store')->name('collections.entries.store');

            Route::group(['prefix' => '{entry}'], function () {
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
                Route::get('{slug}', fn ($collection, $entry, $slug) => redirect($entry->editUrl()));
            });
        });
    });

    Route::group(['namespace' => 'Taxonomies'], function () {
        Route::resource('taxonomies', 'TaxonomiesController');
        Route::resource('taxonomies.blueprints', 'TaxonomyBlueprintsController');
        Route::post('taxonomies/{taxonomy}/blueprints/reorder', 'ReorderTaxonomyBlueprintsController')->name('taxonomies.blueprints.reorder');

        Route::group(['prefix' => 'taxonomies/{taxonomy}/terms'], function () {
            Route::get('/', 'TermsController@index')->name('taxonomies.terms.index');
            Route::post('actions', 'TermActionController@run')->name('taxonomies.terms.actions.run');
            Route::post('actions/list', 'TermActionController@bulkActions')->name('taxonomies.terms.actions.bulk');
            Route::get('create/{site}', 'TermsController@create')->name('taxonomies.terms.create');
            Route::post('create/{site}/preview', 'TermPreviewController@create')->name('taxonomies.terms.preview.create');
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
                Route::post('preview', 'TermPreviewController@edit')->name('taxonomies.terms.preview.edit');
                Route::get('preview', 'TermPreviewController@show')->name('taxonomies.terms.preview.popout');
                Route::patch('/', 'TermsController@update')->name('taxonomies.terms.update');
            });
        });
    });

    Route::group(['namespace' => 'Globals'], function () {
        Route::get('globals', 'GlobalsController@index')->name('globals.index');
        Route::get('globals/create', 'GlobalsController@create')->name('globals.create');
        Route::post('globals', 'GlobalsController@store')->name('globals.store');
        Route::get('globals/{global_set}/edit', 'GlobalsController@edit')->name('globals.edit');
        Route::patch('globals/{global_set}', 'GlobalsController@update')->name('globals.update');
        Route::delete('globals/{global_set}', 'GlobalsController@destroy')->name('globals.destroy');

        Route::get('globals/{global_set}', 'GlobalVariablesController@edit')->name('globals.variables.edit');
        Route::patch('globals/{global_set}/variables', 'GlobalVariablesController@update')->name('globals.variables.update');

        Route::get('globals/{global_set}/blueprint', 'GlobalsBlueprintController@edit')->name('globals.blueprint.edit');
        Route::patch('globals/{global_set}/blueprint', 'GlobalsBlueprintController@update')->name('globals.blueprint.update');
    });

    Route::group(['namespace' => 'Assets'], function () {
        Route::resource('asset-containers', 'AssetContainersController');
        Route::post('asset-containers/{asset_container}/folders', 'FoldersController@store');
        Route::patch('asset-containers/{asset_container}/folders/{path}', 'FoldersController@update')->where('path', '.*');
        Route::get('asset-containers/{asset_container}/blueprint', 'AssetContainerBlueprintController@edit')->name('asset-containers.blueprint.edit');
        Route::patch('asset-containers/{asset_container}/blueprint', 'AssetContainerBlueprintController@update')->name('asset-containers.blueprint.update');
        Route::post('assets/actions', 'ActionController@run')->name('assets.actions.run');
        Route::post('assets/actions/list', 'ActionController@bulkActions')->name('assets.actions.bulk');
        Route::get('assets/browse', 'BrowserController@index')->name('assets.browse.index');
        Route::get('assets/browse/search/{asset_container}/{path?}', 'BrowserController@search')->where('path', '.*');
        Route::post('assets/browse/folders/{asset_container}/actions', 'FolderActionController@run')->name('assets.folders.actions.run');
        Route::get('assets/browse/folders/{asset_container}/{path?}', 'BrowserController@folder')->where('path', '.*');
        Route::get('assets/browse/{asset_container}/{path?}/edit', 'BrowserController@edit')->where('path', '.*')->name('assets.browse.edit');
        Route::get('assets/browse/{asset_container}/{path?}', 'BrowserController@show')->where('path', '.*')->name('assets.browse.show');
        Route::get('assets-fieldtype', 'FieldtypeController@index');
        Route::resource('assets', 'AssetsController')->parameters(['assets' => 'encoded_asset']);
        Route::get('assets/{encoded_asset}/download', 'AssetsController@download')->name('assets.download');
        Route::get('thumbnails/{encoded_asset}/{size?}/{orientation?}', 'ThumbnailController@show')->name('assets.thumbnails.show');
        Route::get('svgs/{encoded_asset}', 'SvgController@show')->name('assets.svgs.show');
        Route::get('pdfs/{encoded_asset}', 'PdfController@show')->name('assets.pdfs.show');
    });

    Route::group(['prefix' => 'fields', 'namespace' => 'Fields'], function () {
        Route::get('/', 'FieldsController@index')->name('fields.index');
        Route::post('edit', 'FieldsController@edit')->name('fields.edit');
        Route::post('update', 'FieldsController@update')->name('fields.update');
        Route::get('field-meta', 'MetaController@show');
        Route::resource('fieldsets', 'FieldsetController');
        Route::get('blueprints', 'BlueprintController@index')->name('blueprints.index');
        Route::get('fieldtypes', 'FieldtypesController@index');
    });

    Route::get('composer/check', 'ComposerOutputController@check');

    Route::group(['namespace' => 'Updater'], function () {
        Route::get('updater', 'UpdaterController@index')->name('updater');
        Route::get('updater/count', 'UpdaterController@count');
        Route::get('updater/{product}', 'UpdateProductController@show')->name('updater.product');
        Route::get('updater/{product}/changelog', 'UpdateProductController@changelog');
        Route::post('updater/{product}/install', 'UpdateProductController@install');
    });

    Route::group(['prefix' => 'duplicates'], function () {
        Route::get('/', 'DuplicatesController@index')->name('duplicates');
        Route::post('regenerate', 'DuplicatesController@regenerate')->name('duplicates.regenerate');
    });

    Route::get('addons', 'AddonsController@index')->name('addons.index');
    Route::post('addons/install', 'AddonsController@install');
    Route::post('addons/uninstall', 'AddonsController@uninstall');
    Route::post('addons/editions', 'AddonEditionsController');

    Route::group(['namespace' => 'Forms'], function () {
        Route::post('forms/actions', 'ActionController@run')->name('forms.actions.run');
        Route::post('forms/actions/list', 'ActionController@bulkActions')->name('forms.actions.bulk');
        Route::post('forms/{form}/submissions/actions', 'SubmissionActionController@run')->name('forms.submissions.actions.run');
        Route::post('forms/{form}/submissions/actions/list', 'SubmissionActionController@bulkActions')->name('forms.submissions.actions.bulk');
        Route::resource('forms', 'FormsController');
        Route::resource('forms.submissions', 'FormSubmissionsController');
        Route::get('forms/{form}/export/{type}', 'FormExportController@export')->name('forms.export');
        Route::get('forms/{form}/blueprint', 'FormBlueprintController@edit')->name('forms.blueprint.edit');
        Route::patch('forms/{form}/blueprint', 'FormBlueprintController@update')->name('forms.blueprint.update');
    });

    Route::group(['namespace' => 'Users'], function () {
        Route::post('users/actions', 'UserActionController@run')->name('users.actions.run');
        Route::post('users/actions/list', 'UserActionController@bulkActions')->name('users.actions.bulk');
        Route::get('users/blueprint', 'UserBlueprintController@edit')->name('users.blueprint.edit');
        Route::patch('users/blueprint', 'UserBlueprintController@update')->name('users.blueprint.update');
        Route::resource('users', 'UsersController');
        Route::patch('users/{user}/password', 'PasswordController@update')->name('users.password.update');
        Route::get('account', 'AccountController')->name('account');
        Route::resource('user-groups', 'UserGroupsController');
        Route::resource('roles', 'RolesController');
    });

    Route::post('user-exists', 'Users\UserWizardController')->name('user.exists');

    Route::get('search', 'SearchController')->name('search');

    Route::get('utilities', 'Utilities\UtilitiesController@index')->name('utilities.index');
    Utility::routes();

    if (config('statamic.graphql.enabled')) {
        Route::get('graphql', 'GraphQLController@index')->name('graphql.index');
        Route::get('graphiql', 'GraphQLController@graphiql')->name('graphql.graphiql');
    }

    Route::group(['prefix' => 'fieldtypes', 'namespace' => 'Fieldtypes'], function () {
        Route::get('relationship', 'RelationshipFieldtypeController@index')->name('relationship.index');
        Route::post('relationship/data', 'RelationshipFieldtypeController@data')->name('relationship.data');
        Route::get('relationship/filters', 'RelationshipFieldtypeController@filters')->name('relationship.filters');
        Route::post('markdown', 'MarkdownFieldtypeController@preview')->name('markdown.preview');
        Route::post('files/upload', 'FilesFieldtypeController@upload')->name('files.upload');
    });

    Route::group(['prefix' => 'api', 'as' => 'api.', 'namespace' => 'API'], function () {
        Route::resource('addons', 'AddonsController');
        Route::resource('templates', 'TemplatesController');
    });

    Route::group(['prefix' => 'preferences', 'as' => 'preferences.', 'namespace' => 'Preferences'], function () {
        Route::get('/', 'PreferenceController@index')->name('index');
        Route::get('edit', 'UserPreferenceController@edit')->name('user.edit');
        Route::patch('/', 'UserPreferenceController@update')->name('user.update');

        Route::middleware([RequireStatamicPro::class, 'can:manage preferences'])->group(function () {
            Route::get('roles/{role}/edit', 'RolePreferenceController@edit')->name('role.edit');
            Route::patch('roles/{role}', 'RolePreferenceController@update')->name('role.update');
            Route::get('default/edit', 'DefaultPreferenceController@edit')->name('default.edit');
            Route::patch('default', 'DefaultPreferenceController@update')->name('default.update');
        });

        Route::post('js', 'PreferenceController@store')->name('store');
        Route::delete('js/{key}', 'PreferenceController@destroy')->name('destroy');

        Route::group(['prefix' => 'nav', 'as' => 'nav.', 'namespace' => 'Nav'], function () {
            Route::get('/', 'NavController@index')->name('index');
            Route::get('edit', 'UserNavController@edit')->name('user.edit');
            Route::patch('/', 'UserNavController@update')->name('user.update');
            Route::delete('/', 'UserNavController@destroy')->name('user.destroy');

            Route::middleware([RequireStatamicPro::class, 'can:manage preferences'])->group(function () {
                Route::get('roles/{role}/edit', 'RoleNavController@edit')->name('role.edit');
                Route::patch('roles/{role}', 'RoleNavController@update')->name('role.update');
                Route::delete('roles/{role}', 'RoleNavController@destroy')->name('role.destroy');
                Route::get('default/edit', 'DefaultNavController@edit')->name('default.edit');
                Route::patch('default', 'DefaultNavController@update')->name('default.update');
                Route::delete('default', 'DefaultNavController@destroy')->name('default.destroy');
            });
        });
    });

    Route::get('session-timeout', 'SessionTimeoutController')->name('session.timeout');

    Route::view('/playground', 'statamic::playground')->name('playground');

    Route::get('{segments}', 'CpController@pageNotFound')->where('segments', '.*')->name('404');
});
