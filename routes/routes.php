<?php

if (config('statamic.api.enabled')) {
    Route::middleware(config('statamic.api.middleware'))
        ->name('statamic.api.')
        ->prefix(config('statamic.api.route'))
        ->namespace('Statamic\Http\Controllers\API')
        ->group(__DIR__.'/api.php');
}

if (config('statamic.cp.enabled')) {
    Route::middleware('web')
        ->name('statamic.cp.')
        ->prefix(config('statamic.cp.route'))
        ->namespace('Statamic\Http\Controllers\CP')
        ->group(__DIR__.'/cp.php');
}

if (config('statamic.routes.enabled')) {
    Route::middleware('web')
        ->namespace('Statamic\Http\Controllers')
        ->group(__DIR__.'/web.php');
}
