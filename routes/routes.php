<?php

use Illuminate\Support\Facades\Route;
use Statamic\API\Middleware\Cache;
use Statamic\Facades\Glide;
use Statamic\Http\Middleware\API\SwapExceptionHandler as SwapAPIExceptionHandler;
use Statamic\Http\Middleware\CP\SwapExceptionHandler as SwapCpExceptionHandler;
use Statamic\Http\Middleware\RequireStatamicPro;

if (config('statamic.api.enabled')) {
    Route::middleware([
        SwapApiExceptionHandler::class,
        RequireStatamicPro::class,
        Cache::class,
    ])->group(function () {
        Route::middleware(config('statamic.api.middleware'))
            ->name('statamic.api.')
            ->prefix(config('statamic.api.route'))
            ->namespace('Statamic\Http\Controllers\API')
            ->group(__DIR__.'/api.php');
    });
}

if (config('statamic.cp.enabled')) {
    Route::middleware(SwapCpExceptionHandler::class)->group(function () {
        Route::middleware('statamic.cp')
            ->name('statamic.cp.')
            ->prefix(config('statamic.cp.route'))
            ->namespace('Statamic\Http\Controllers\CP')
            ->group(__DIR__.'/cp.php');
    });
}

if (Glide::shouldServeByHttp()) {
    Route::namespace('Statamic\Http\Controllers')
        ->group(__DIR__.'/glide.php');
}

Route::middleware(config('statamic.routes.middleware', 'web'))
    ->namespace('Statamic\Http\Controllers')
    ->group(__DIR__.'/web.php');
