<?php

namespace Statamic\CP\Utilities;

use Statamic\Facades\Utility;
use Statamic\Http\Controllers\CP\Utilities\CacheController;
use Statamic\Http\Controllers\CP\Utilities\EmailController;
use Statamic\Http\Controllers\CP\Utilities\PhpInfoController;
use Statamic\Http\Controllers\CP\Utilities\UpdateSearchController;
use Statamic\Statamic;

class CoreUtilities
{
    public static function boot()
    {
        Utility::make('cache')
            ->action([CacheController::class, 'index'])
            ->title(__('Cache Manager'))
            ->navTitle(__('Cache'))
            ->description(__('statamic::messages.cache_utility_description'))
            ->docsUrl(Statamic::docsUrl('utilities/cache-manager'))
            ->routes(function ($router) {
                $router->post('cache/{cache}', [CacheController::class, 'clear'])->name('clear');
            })
            ->register();

        Utility::make('phpinfo')
            ->action(PhpInfoController::class)
            ->title(__('PHP Info'))
            ->description(__('statamic::messages.phpinfo_utility_description'))
            ->docsUrl(Statamic::docsUrl('utilities/phpinfo'))
            ->register();

        Utility::make('search')
            ->view('statamic::utilities.search')
            ->title(__('Search'))
            ->description(__('statamic::messages.search_utility_description'))
            ->docsUrl(Statamic::docsUrl('utilities/search'))
            ->routes(function ($router) {
                $router->post('/', [UpdateSearchController::class, 'update'])->name('update');
            })
            ->register();

        Utility::make('email')
            ->view('statamic::utilities.email')
            ->title(__('Email'))
            ->description(__('statamic::messages.email_utility_description'))
            ->docsUrl(Statamic::docsUrl('utilities/email'))
            ->routes(function ($router) {
                $router->post('/', [EmailController::class, 'send']);
            })
            ->register();
    }
}
