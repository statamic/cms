<?php

use Statamic\Http\Middleware\CP\Configurable;

Route::group(['prefix' => 'auth'], function () {
    Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
    Route::post('login', 'Auth\LoginController@login');
    Route::get('logout', 'Auth\LoginController@logout')->name('logout');
});

Route::group(['middleware' => 'auth'], function () {
    Route::redirect('/', 'cp/dashboard')->name('cp');
    Route::get('dashboard', 'DashboardController@index')->name('dashboard');

    Route::get('licensing', 'LicensingController@index')->name('licensing');
    Route::get('licensing/refresh', 'LicensingController@refresh')->name('licensing.refresh');
    Route::post('licensing', 'LicensingController@update')->name('licensing.update');

    Route::group(['prefix' => 'pages'], function () {
        Route::get('/', 'PagesController@pages')->name('pages');
        Route::post('/', 'PagesController@save')->name('pages.post');
        Route::get('/get', 'PagesController@get')->name('pages.get');
        Route::post('/delete', 'PagesController@delete')->name('page.delete');
        Route::post('publish', 'PublishPageController@save')->name('page.save');
        Route::get('create/{parent?}', 'PublishPageController@create')->name('page.create')->where('parent', '.*');
        Route::get('edit/{url?}', ['uses' => 'PublishPageController@edit', 'as' => 'page.edit'])->where('url', '.*');
        Route::post('mount', ['uses' => 'PagesController@mountCollection', 'as' => 'page.mount']);
        Route::post('duplicate', 'DuplicatePageController@store');
    });

    Route::group(['prefix' => 'collections'], function () {
        Route::get('/', 'CollectionsController@index')->name('collections');
        Route::get('get', 'CollectionsController@get')->name('collections.get');
    });

    Route::group(['prefix' => 'collections/entries'], function () {
        Route::get('/', 'EntriesController@index')->name('entries');
        Route::delete('delete', 'EntriesController@delete')->name('entries.delete');
        Route::get('/{collection}/get', 'EntriesController@get')->name('entries.get');
        Route::get('/{collection}/search', 'EntriesSearchController@search')->name('entries.search');
        Route::post('reorder', 'EntriesController@reorder')->name('entries.reorder');
        Route::get('/{collection}/create', 'PublishEntryController@create')->name('entry.create');
        Route::post('/{collection}/duplicate', 'DuplicateEntryController@store')->name('entry.duplicate');
        Route::get('/{collection}/{slug}', ['uses' => 'PublishEntryController@edit', 'as' => 'entry.edit']);
        Route::post('publish', 'PublishEntryController@save')->name('entry.save');
        Route::get('/{collection}', 'EntriesController@show')->name('entries.show');
    });

    Route::group(['prefix' => 'taxonomies'], function () {
        Route::get('/', 'TaxonomiesController@index')->name('taxonomies');
        Route::get('get', 'TaxonomiesController@get')->name('taxonomies.get');
    });

    Route::group(['prefix' => 'taxonomies/terms'], function () {
        Route::get('/', 'TaxonomyTermsController@index')->name('terms');
        Route::delete('delete', 'TaxonomyTermsController@delete')->name('terms.delete');
        Route::get('/{collection}/get', 'TaxonomyTermsController@get')->name('terms.get');
        Route::get('/{collection}/create', 'PublishTaxonomyController@create')->name('term.create');
        Route::get('/{collection}/{slug}', 'PublishTaxonomyController@edit')->name('term.edit');
        Route::post('publish', 'PublishTaxonomyController@save')->name('taxonomy.save');
        Route::get('/{collection}', 'TaxonomyTermsController@show')->name('terms.show');
    });

    Route::group(['prefix' => 'globals'], function () {
        Route::get('/', 'GlobalsController@index')->name('globals');
        Route::get('get', 'GlobalsController@get')->name('globals.get');
        Route::get('{slug}', ['uses' => 'PublishGlobalController@edit', 'as' => 'globals.edit']);
        Route::post('publish', 'PublishGlobalController@save')->name('global.save');
    });

    Route::group(['prefix' => 'assets'], function () {
        Route::get('/', 'AssetsController@index')->name('assets');

        Route::group(['prefix' => 'containers'], function () {
            Route::delete('delete', 'AssetContainersController@delete')->name('assets.containers.delete');
            Route::get('get', 'AssetContainersController@get')->name('assets.containers.get');
            Route::post('resolve-path', 'AssetContainersController@getResolvedPath');
            Route::post('resolve-url', 'AssetContainersController@getResolvedUrl');
            Route::post('validate-s3', 'AssetContainersController@validateS3Credentials');
            Route::get('{container}/folders', 'AssetContainersController@folders')->name('assets.containers.folders');
        });

        Route::group(['prefix' => 'folders'], function () {
            Route::post('/', 'AssetFoldersController@store')->name('assets.folder.store');
            Route::delete('delete', 'AssetFoldersController@delete')->name('assets.folders.delete');
            Route::get('{container}/{path?}', 'AssetFoldersController@edit')->where('path',
                '.*')->name('assets.folder.edit');
            Route::post('{container}/{path?}', 'AssetFoldersController@update')->where('path',
                '.*')->name('assets.folder.update');
        });

        Route::get('thumbnails/{asset}/{size?}', 'AssetThumbnailController@show')->name('asset.thumbnail');

        Route::post('get', 'AssetsController@get')->name('assets.get');
        Route::delete('delete', 'AssetsController@delete')->name('asset.delete');
        Route::get('browse/{container}/{folder?}', 'AssetsController@browse')->where('folder', '.*')->name('assets.browse');
        Route::post('browse', 'AssetsController@json');
        Route::post('search', 'AssetsController@search');
        Route::post('/', 'AssetsController@store')->name('asset.store');
        Route::get('download/{container}/{path}', 'AssetsController@download')->name('asset.download')->where('path', '.*');
        Route::post('rename/{container}/{path}', 'AssetsController@rename')->name('asset.rename')->where('path', '.*');
        Route::post('move', 'AssetsController@move')->name('asset.move');
        Route::get('{container}/{path}', 'AssetsController@edit')->name('asset.edit')->where('path', '.*');
        Route::post('{container}/{path}', 'AssetsController@update')->name('asset.update')->where('path', '.*');
    });

    Route::group(['prefix' => 'users'], function () {
        Route::get('account', 'UsersController@account')->name('account');
        Route::get('/', 'UsersController@index')->name('users');
        Route::get('get', 'UsersController@get')->name('users.get');
        Route::get('create', 'UsersController@create')->name('user.create');
        Route::delete('delete', 'UsersController@delete')->name('users.delete');
        Route::post('publish', 'PublishUserController@save')->name('user.save');

        Route::group(['prefix' => 'roles'], function () {
            Route::get('/', 'RolesController@index')->name('user.roles');
            Route::get('get', 'RolesController@get')->name('user.roles.get');
            Route::get('create', 'RolesController@create')->name('user.role.create');
            Route::post('/', 'RolesController@store')->name('user.role.store');
            Route::delete('delete', 'RolesController@delete')->name('user.roles.delete');
            Route::get('roles', 'RolesController@getRoles');
            Route::get('{role}', 'RolesController@edit')->name('user.role');
            Route::post('{role}', 'RolesController@update')->name('user.role');
        });

        Route::group(['prefix' => 'groups'], function () {
            Route::get('/', 'UserGroupsController@index')->name('user.groups');
            Route::get('get', 'UserGroupsController@get')->name('user.groups.get');
            Route::get('create', 'UserGroupsController@create')->name('user.group.create');
            Route::post('/', 'UserGroupsController@store')->name('user.group.store');
            Route::delete('delete', 'UserGroupsController@delete')->name('user.groups.delete');
            Route::get('groups', 'UserGroupsController@getGroups');
            Route::get('{group}', 'UserGroupsController@edit')->name('user.group');
            Route::post('{group}', 'UserGroupsController@update')->name('user.group');
        });

        Route::get('{username}', ['uses' => 'UsersController@edit', 'as' => 'user.edit']);
        Route::get('{username}/reset-url', 'UsersController@getResetUrl');
        Route::get('{username}/send-reset-email', 'UsersController@sendResetEmail');
    });

    Route::get('system/templates/get', 'CpController@templates');

    Route::group(['prefix' => 'fieldsets'], function () {
        Route::get('get', 'FieldsetController@get')->name('fieldsets.get');
        Route::get('{fieldset}/get', 'FieldsetController@getFieldset')->name('fieldset.get');

        Route::group(['middleware' => Configurable::class], function () {
            Route::get('/', 'FieldsetController@index')->name('fieldsets');
            Route::get('/create', 'FieldsetController@create')->name('fieldset.create');
            Route::post('/update-layout/{fieldset}', 'FieldsetController@updateLayout')->name('fieldset.update-layout');
            Route::delete('delete', 'FieldsetController@delete')->name('fieldsets.delete');
            Route::post('quick', 'FieldsetController@quickStore');
            Route::get('/{fieldset}', 'FieldsetController@edit')->name('fieldset.edit');
            Route::post('/{fieldset}', 'FieldsetController@update')->name('fieldset.update');
            Route::post('/', 'FieldsetController@store')->name('fieldset.store');
        });
    });

    Route::get('fieldtypes', 'FieldtypesController@index')->name('fieldtypes');

    Route::group(['prefix' => 'addons', 'middleware' => Configurable::class], function () {
        Route::get('/', 'AddonsController@index')->name('addons');
        Route::get('get', 'AddonsController@get')->name('addons.get');
    });

    Route::group(['prefix' => 'addons', 'middleware' => Configurable::class], function () {
        Route::get('{addon}/settings', 'AddonsController@settings')->name('addon.settings');
        Route::post('{addon}/settings', 'AddonsController@saveSettings');
    });

    Route::post('addons/suggest/suggestions', '\Statamic\Addons\Suggest\SuggestController@suggestions');

    Route::get('updater', 'UpdaterController@index')->name('updater');
    Route::get('updater/{version}', 'UpdaterController@update')->name('updater.update');

    Route::get('resolve-duplicate-ids', 'DuplicateIdController@index')->name('resolve-duplicate-ids');
    Route::post('resolve-duplicate-ids', 'DuplicateIdController@update')->name('resolve-duplicate-ids.update');
});

// Just to make stuff work.
Route::get('/account', function () { return ''; })->name('account');
Route::get('/forms', function () { return ''; })->name('forms');
Route::get('/content', function () { return ''; })->name('content');
Route::get('/assets.containers.manage', function () { return ''; })->name('assets.containers.manage');
Route::get('/assets.container.edit', function () { return ''; })->name('assets.container.edit');
Route::get('/collections.manage', function () { return ''; })->name('collections.manage');
Route::get('/collection.edit', function () { return ''; })->name('collection.edit');
Route::get('/taxonomies.manage', function () { return ''; })->name('taxonomies.manage');
Route::get('/taxonomy.edit', function () { return ''; })->name('taxonomy.edit');
Route::get('/taxonomy.create', function () { return ''; })->name('taxonomy.create');
Route::get('/globals.manage', function () { return ''; })->name('globals.manage');
Route::get('/fieldsets', function () { return ''; })->name('fieldsets');
Route::get('/form.show', function () { return ''; })->name('form.show');
Route::get('/login.reset', function () { return ''; })->name('login.reset');

Route::get('{segments}', 'CpController@pageNotFound')->where('segments', '.*')->name('404');
