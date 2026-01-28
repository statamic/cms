<?php

use Illuminate\Support\Facades\Route;
use Statamic\API\Middleware\Cache;
use Statamic\API\Middleware\HandleAuthentication;
use Statamic\Facades\Glide;
use Statamic\Http\Middleware\CP\SwapExceptionHandler as SwapCpExceptionHandler;
use Statamic\Http\Middleware\RequireStatamicPro;

if (config('statamic.api.enabled')) {
    Route::middleware([
        RequireStatamicPro::class,
        HandleAuthentication::class,
        Cache::class,
    ])->group(function () {
        Route::middleware(config('statamic.api.middleware'))
            ->name('statamic.api.')
            ->prefix(config('statamic.api.route'))
            ->group(__DIR__.'/api.php');
    });
}

if (config('statamic.cp.enabled')) {
    Route::middleware(SwapCpExceptionHandler::class)->group(function () {
        Route::middleware('statamic.cp')
            ->name('statamic.cp.')
            ->prefix(config('statamic.cp.route'))
            ->group(__DIR__.'/cp.php');
    });
}

if (Glide::shouldServeByHttp()) {
    require __DIR__.'/glide.php';
}

Route::middleware(config('statamic.routes.middleware', 'web'))
    ->group(__DIR__.'/web.php');
