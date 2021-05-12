<?php

use Illuminate\Support\Facades\Route;

Route::resource('collections.entries', 'CollectionEntriesController')->only('index', 'show');
Route::resource('taxonomies.terms', 'TaxonomyTermsController')->only('index', 'show');
Route::resource('taxonomies.terms.entries', 'TaxonomyTermEntriesController')->only('index');
Route::resource('globals', 'GlobalsController')->only('index', 'show');
Route::resource('forms', 'FormsController')->only('index', 'show');
Route::resource('users', 'UsersController')->only('index', 'show');

Route::name('assets.index')->get('assets/{asset_container}', 'AssetsController@index');
Route::name('assets.show')->get('assets/{asset_container}/{asset}', 'AssetsController@show')->where('asset', '.*');

Route::get('collections/{collection}/tree', 'CollectionTreeController@show');
Route::get('navs/{nav}/tree', 'NavigationTreeController@show');

Route::get('{path?}', 'NotFoundController')->where('path', '.*');
