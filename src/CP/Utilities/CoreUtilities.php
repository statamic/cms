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
use function Statamic\trans as __;

class CoreUtilities
{
    public static function boot()
    {
        Utility::register('cache')
            ->action([CacheController::class, 'index'])
            ->title(__('Cache Manager'))
            ->icon('cache')
            ->navTitle(__('Cache'))
            ->description(__('statamic::messages.cache_utility_description'))
            ->docsUrl(Statamic::docsUrl('utilities/cache-manager'))
            ->routes(function ($router) {
                $router->post('cache/{cache}', [CacheController::class, 'clear'])->name('clear');
                $router->post('cache/{cache}/warm', [CacheController::class, 'warm'])->name('warm');
            });

        Utility::register('phpinfo')
            ->action(PhpInfoController::class)
            ->title(__('PHP Info'))
            ->icon('php')
            ->description(__('statamic::messages.phpinfo_utility_description'))
            ->docsUrl(Statamic::docsUrl('utilities/phpinfo'));

        Utility::register('search')
            ->view('statamic::utilities.search')
            ->title(__('Search'))
            ->icon('search-utility')
            ->description(__('statamic::messages.search_utility_description'))
            ->routes(function ($router) {
                $router->post('/', [UpdateSearchController::class, 'update'])->name('update');
            });

        Utility::register('email')
            ->view('statamic::utilities.email')
            ->title(__('Email'))
            ->icon('email-utility')
            ->description(__('statamic::messages.email_utility_description'))
            ->docsUrl(Statamic::docsUrl('utilities/email'))
            ->routes(function ($router) {
                $router->post('/', [EmailController::class, 'send']);
            });

        Utility::register('licensing')
            ->action([LicensingController::class, 'show'])
            ->title(__('Licensing'))
            ->icon('licensing')
            ->description(__('statamic::messages.licensing_utility_description'))
            ->docsUrl(Statamic::docsUrl('licensing'))
            ->routes(function ($router) {
                $router->get('refresh', [LicensingController::class, 'refresh'])->name('refresh');
            });

        if (config('statamic.git.enabled') && Statamic::pro()) {
            Utility::register('git')
                ->action([GitController::class, 'index'])
                ->title('Git')
                ->icon('git')
                ->description(__('statamic::messages.git_utility_description'))
                ->docsUrl(Statamic::docsUrl('utilities/git'))
                ->routes(function ($router) {
                    $router->post('/', [GitController::class, 'commit'])->name('commit');
                });
        }
    }
}
