<?php

use Illuminate\Support\Facades\Route;

if (config('statamic.api.enabled')) {
    Route::middleware(config('statamic.api.middleware'))
        ->name('statamic.api.')
        ->prefix(config('statamic.api.route'))
        ->namespace('Statamic\Http\Controllers\API')
        ->group(__DIR__.'/api.php');
}

if (config('statamic.cp.enabled')) {
    Route::middleware('statamic-cp')
        ->name('statamic.cp.')
        ->prefix(config('statamic.cp.route'))
        ->namespace('Statamic\Http\Controllers\CP')
        ->group(__DIR__.'/cp.php');
}

if (config('statamic.routes.enabled')) {
    Route::middleware(config('statamic.routes.middleware', 'web'))
        ->namespace('Statamic\Http\Controllers')
        ->group(__DIR__.'/web.php');
}
