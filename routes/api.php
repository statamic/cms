<?php

use Illuminate\Support\Facades\Route;
use Statamic\Http\Controllers\API\AssetsController;
use Statamic\Http\Controllers\API\CollectionEntriesController;
use Statamic\Http\Controllers\API\CollectionTreeController;
use Statamic\Http\Controllers\API\FormsController;
use Statamic\Http\Controllers\API\GlobalsController;
use Statamic\Http\Controllers\API\NavigationTreeController;
use Statamic\Http\Controllers\API\NotFoundController;
use Statamic\Http\Controllers\API\TaxonomyTermEntriesController;
use Statamic\Http\Controllers\API\TaxonomyTermsController;
use Statamic\Http\Controllers\API\UsersController;

Route::resource('collections.entries', CollectionEntriesController::class)->only('index', 'show');
Route::resource('taxonomies.terms', TaxonomyTermsController::class)->only('index', 'show');
Route::resource('taxonomies.terms.entries', TaxonomyTermEntriesController::class)->only('index');
Route::resource('globals', GlobalsController::class)->only('index', 'show');
Route::resource('forms', FormsController::class)->only('index', 'show');
Route::resource('users', UsersController::class)->only('index', 'show');

Route::name('assets.index')->get('assets/{asset_container}', [AssetsController::class, 'index']);
Route::name('assets.show')->get('assets/{asset_container}/{asset}', [AssetsController::class, 'show'])->where('asset', '.*');

Route::get('collections/{collection}/tree', [CollectionTreeController::class, 'show']);
Route::get('navs/{nav}/tree', [NavigationTreeController::class, 'show']);

Route::get('{path?}', NotFoundController::class)->where('path', '.*');
