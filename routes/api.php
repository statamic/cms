<?php

Route::resource('collections.entries', 'CollectionEntriesController');
Route::resource('taxonomies.terms', 'TaxonomyTermsController');
Route::resource('globals', 'GlobalsController');
Route::resource('users', 'UsersController');

Route::name('assets.index')->get('assets/{asset_container}', 'AssetsController@index');
Route::name('assets.show')->get('assets/{asset_container}/{asset}', 'AssetsController@show')->where('asset', '.*');
