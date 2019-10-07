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
            ->title('Cache Manager')
            ->navTitle('Cache')
            ->description('Manage and view important information about Statamic\'s various caching layers.')
            ->docsUrl(Statamic::docsUrl('utilities/cache-manager'))
            ->routes(function ($router) {
                $router->post('cache/{cache}', [CacheController::class, 'clear'])->name('utilities.cache.clear');
            })
            ->register();

        Utility::make('phpinfo')
            ->action(PhpInfoController::class)
            ->title('PHP Info')
            ->description('Check your PHP configuration settings and installed modules.')
            ->docsUrl(Statamic::docsUrl('utilities/phpinfo'))
            ->register();

        Utility::make('search')
            ->action([UpdateSearchController::class, 'index'])
            ->title('Search')
            ->description('Manage and view important information about Statamic\'s search indexes.')
            ->docsUrl(Statamic::docsUrl('utilities/search'))
            ->routes(function ($router) {
                $router->post('search', [UpdateSearchController::class, 'update']);
            })
            ->register();

        Utility::make('email')
            ->action([EmailController::class, 'index'])
            ->title('Email')
            ->description('Check email configuration and send a test.')
            ->docsUrl(Statamic::docsUrl('utilities/email'))
            ->routes(function ($router) {
                $router->post('email', [EmailController::class, 'send']);
            })
            ->register();
    }
}