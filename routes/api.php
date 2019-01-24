<?php

Route::resource('assets', 'AssetsController');
Route::resource('collections', 'CollectionsController');
Route::resource('collections/{collection}/entries', 'CollectionEntriesController');
Route::resource('forms', 'FormsController');
Route::resource('globals', 'GlobalsController');
Route::resource('taxonomies', 'TaxonomiesController');
Route::resource('taxonomies/{taxonomy}/terms', 'TaxonomyTermsController');
Route::resource('users', 'UsersController');
