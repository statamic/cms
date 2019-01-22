<?php

Route::group(['prefix' => 'api', 'as' => 'api'], function () {
    Route::resource('addons', 'AddonsController');
    Route::resource('assets', 'AssetsController');
    Route::resource('collections', 'CollectionsController');
    Route::resource('collections/{collection}/entries', 'CollectionEntriesController');
    Route::resource('users', 'UsersController');

    // Route::resource('taxonomies', 'TaxonomiesController');
    // Route::resource('taxonomies/{taxonomy}/terms', 'TaxonomyTermsController');
    // Route::resource('globals', 'GlobalsController');
    // Route::resource('forms', 'FormsController');
    // Route::resource('forms/{form}/submissions', 'FormSubmissionsController');
});
