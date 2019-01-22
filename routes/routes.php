<?php

Route::middleware('api')
     ->namespace('Statamic\Http\Controllers\API')
     ->group(__DIR__.'/api.php');

Route::middleware('web')
    ->name('statamic.cp.')
    ->prefix(config('statamic.cp.route', 'cp'))
    ->namespace('Statamic\Http\Controllers\CP')
    ->group(__DIR__.'/cp.php');

Route::middleware('web')
     ->namespace('Statamic\Http\Controllers')
     ->group(__DIR__.'/web.php');