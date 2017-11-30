<?php

Route::group(['prefix' => 'auth'], function () {
    Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
    Route::post('login', 'Auth\LoginController@login');
    Route::get('logout', 'Auth\LoginController@logout')->name('logout');
});

Route::group(['middleware' => 'auth'], function () {
    Route::redirect('/', 'cp/dashboard')->name('cp');
    Route::get('dashboard', 'DashboardController@index')->name('dashboard');
});

// Just to make stuff work.
Route::get('/page-edit', function () { return ''; })->name('page.edit');
Route::get('/entry-edit', function () { return ''; })->name('entry.edit');
Route::get('/collections/entries/{collection}', function () { return ''; })->name('entries.show');
Route::get('/term.edit', function () { return ''; })->name('term.edit');
Route::get('/account', function () { return ''; })->name('account');
Route::get('/pages', function () { return ''; })->name('pages');
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

