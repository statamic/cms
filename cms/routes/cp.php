<?php

Route::get('/', 'BaseController@index');

// Just to make stuff work.
Route::get('/page-edit', function () { return ''; })->name('page.edit');
Route::get('/entry-edit', function () { return ''; })->name('entry.edit');
Route::get('/collections/entries/{collection}', function () { return ''; })->name('entries.show');
Route::get('/term.edit', function () { return ''; })->name('term.edit');
