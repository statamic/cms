<?php

Route::group(['prefix' => 'auth'], function () {
    Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
    Route::post('login', 'Auth\LoginController@login');
    Route::get('logout', 'Auth\LoginController@logout')->name('logout');
});

Route::group(['middleware' => 'auth'], function () {
    Route::get('/', function () { return view('dashboard');  })->name('cp');
});

// Just to make stuff work.
Route::get('/page-edit', function () { return ''; })->name('page.edit');
Route::get('/entry-edit', function () { return ''; })->name('entry.edit');
Route::get('/collections/entries/{collection}', function () { return ''; })->name('entries.show');
Route::get('/term.edit', function () { return ''; })->name('term.edit');
Route::get('/account', function () { return ''; })->name('account');
