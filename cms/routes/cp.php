<?php

Route::get('/', 'BaseController@index');

// Just to make stuff work.
Route::get('/page-edit', function () { return ''; })->name('page.edit');
Route::get('/entry-edit', function () { return ''; })->name('entry.edit');
