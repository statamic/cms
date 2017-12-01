<?php

Route::group(['prefix' => 'auth'], function () {
    Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
    Route::post('login', 'Auth\LoginController@login');
    Route::get('logout', 'Auth\LoginController@logout')->name('logout');
});

Route::group(['middleware' => 'auth'], function () {
    Route::redirect('/', 'cp/dashboard')->name('cp');
    Route::get('dashboard', 'DashboardController@index')->name('dashboard');

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


    Route::group(['prefix' => 'fieldsets'], function () {
        Route::get('get', 'FieldsetController@get')->name('fieldsets.get');
        Route::get('{fieldset}/get', 'FieldsetController@getFieldset')->name('fieldset.get');

        Route::group(['middleware' => 'configurable'], function () {
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
});

// Just to make stuff work.
Route::get('/account', function () { return ''; })->name('account');
Route::get('/assets', function () { return ''; })->name('assets');
Route::get('/forms', function () { return ''; })->name('forms');
Route::get('/updater', function () { return ''; })->name('updater');
Route::get('/import', function () { return ''; })->name('import');
Route::get('/addons', function () { return ''; })->name('addons');
Route::get('/content', function () { return ''; })->name('content');
Route::get('/assets.containers.manage', function () { return ''; })->name('assets.containers.manage');
Route::get('/collections.manage', function () { return ''; })->name('collections.manage');
Route::get('/collection.edit', function () { return ''; })->name('collection.edit');
Route::get('/taxonomies.manage', function () { return ''; })->name('taxonomies.manage');
Route::get('/taxonomy.edit', function () { return ''; })->name('taxonomy.edit');
Route::get('/taxonomy.create', function () { return ''; })->name('taxonomy.create');
Route::get('/globals.manage', function () { return ''; })->name('globals.manage');
Route::get('/fieldsets', function () { return ''; })->name('fieldsets');
Route::get('/settings', function () { return ''; })->name('settings');
Route::get('/settings.edit', function () { return ''; })->name('settings.edit');
Route::get('/form.show', function () { return ''; })->name('form.show');

