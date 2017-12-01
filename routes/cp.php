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
        Route::get('get', 'CollectionsController@get')->name('collections.get');
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
Route::get('/entry-edit', function () { return ''; })->name('entry.edit');
Route::get('/collections/entries/{collection}', function () { return ''; })->name('entries.show');
Route::get('/term.edit', function () { return ''; })->name('term.edit');
Route::get('/account', function () { return ''; })->name('account');
Route::get('/collections', function () { return ''; })->name('collections');
Route::get('/taxonomies', function () { return ''; })->name('taxonomies');
Route::get('/assets', function () { return ''; })->name('assets');
Route::get('/globals', function () { return ''; })->name('globals');
Route::get('/forms', function () { return ''; })->name('forms');
Route::get('/updater', function () { return ''; })->name('updater');
Route::get('/import', function () { return ''; })->name('import');
Route::get('/addons', function () { return ''; })->name('addons');
Route::get('/content', function () { return ''; })->name('content');
Route::get('/assets.containers.manage', function () { return ''; })->name('assets.containers.manage');
Route::get('/collections.manage', function () { return ''; })->name('collections.manage');
Route::get('/collection.edit', function () { return ''; })->name('collection.edit');
Route::get('/taxonomies.manage', function () { return ''; })->name('taxonomies.manage');
Route::get('/globals.manage', function () { return ''; })->name('globals.manage');
Route::get('/fieldsets', function () { return ''; })->name('fieldsets');
Route::get('/settings', function () { return ''; })->name('settings');
Route::get('/users', function () { return ''; })->name('users');
Route::get('/user.groups', function () { return ''; })->name('user.groups');
Route::get('/user.roles', function () { return ''; })->name('user.roles');
Route::get('/settings.edit', function () { return ''; })->name('settings.edit');
Route::get('/entry.create', function () { return ''; })->name('entry.create');
Route::get('/form.show', function () { return ''; })->name('form.show');

