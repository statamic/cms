<?php

namespace Statamic\CP\Utilities;

use Statamic\Facades\Utility;
use Statamic\Http\Controllers\CP\LicensingController;
use Statamic\Http\Controllers\CP\Utilities\CacheController;
use Statamic\Http\Controllers\CP\Utilities\EmailController;
use Statamic\Http\Controllers\CP\Utilities\GitController;
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
            ->icon('cache')
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
            ->icon('php')
            ->description(__('statamic::messages.phpinfo_utility_description'))
            ->docsUrl(Statamic::docsUrl('utilities/phpinfo'))
            ->register();

        Utility::make('search')
            ->view('statamic::utilities.search')
            ->title(__('Search'))
            ->icon('search-utility')
            ->description(__('statamic::messages.search_utility_description'))
            ->docsUrl(Statamic::docsUrl('utilities/search'))
            ->routes(function ($router) {
                $router->post('/', [UpdateSearchController::class, 'update'])->name('update');
            })
            ->register();

        Utility::make('email')
            ->view('statamic::utilities.email')
            ->title(__('Email'))
            ->icon('email-utility')
            ->description(__('statamic::messages.email_utility_description'))
            ->docsUrl(Statamic::docsUrl('utilities/email'))
            ->routes(function ($router) {
                $router->post('/', [EmailController::class, 'send']);
            })
            ->register();

        Utility::make('licensing')
            ->action([LicensingController::class, 'show'])
            ->title(__('Licensing'))
            ->icon('licensing')
            ->description(__('statamic::messages.licensing_utility_description'))
            ->docsUrl(Statamic::docsUrl('licensing'))
            ->routes(function ($router) {
                $router->get('refresh', [LicensingController::class, 'refresh'])->name('refresh');
            })
            ->register();

        if (config('statamic.git.enabled') && Statamic::pro()) {
            Utility::make('git')
                ->action([GitController::class, 'index'])
                ->title('Git')
                ->icon('git')
                ->description(__('statamic::messages.git_utility_description'))
                ->docsUrl(Statamic::docsUrl('utilities/git'))
                ->routes(function ($router) {
                    $router->post('/', [GitController::class, 'commit'])->name('commit');
                })
                ->register();
        }
    }
}
