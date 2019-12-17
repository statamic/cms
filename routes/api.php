<?php

Route::resource('assets', 'AssetsController');
Route::resource('collections/{collection}/entries', 'CollectionEntriesController');
Route::resource('globals', 'GlobalsController');
Route::resource('taxonomies/{taxonomy}/terms', 'TaxonomyTermsController');
Route::resource('users', 'UsersController');
