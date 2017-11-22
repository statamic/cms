<?php

/**
 * Front-end
 * All front-end website requests go through a single controller method.
 */
Route::any('/{segments?}', 'FrontendController@index')->where('segments', '.*')->name('site')->middleware(['staticcache']);
