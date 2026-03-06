<?php

namespace Statamic\CP\Utilities;

use Statamic\Facades\Search;
use Statamic\Facades\User;
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
                $router->post('clear-all', [CacheController::class, 'clearAll'])->name('clear-all');
                $router->post('clear-stache', [CacheController::class, 'clearStacheCache'])->name('clear-stache');
                $router->post('warm-stache', [CacheController::class, 'warmStacheCache'])->name('warm-stache');
                $router->post('clear-static', [CacheController::class, 'clearStaticCache'])->name('clear-static');
                $router->post('invalidate-static-pages', [CacheController::class, 'invalidateStaticUrls'])->name('invalidate-static-pages');
                $router->post('clear-application-cache', [CacheController::class, 'clearApplicationCache'])->name('clear-application-cache');
                $router->post('clear-image-cache', [CacheController::class, 'clearImageCache'])->name('clear-image-cache');
            });

        Utility::register('phpinfo')
            ->action(PhpInfoController::class)
            ->title(__('PHP Info'))
            ->icon('info')
            ->description(__('statamic::messages.phpinfo_utility_description'))
            ->docsUrl(Statamic::docsUrl('utilities/phpinfo'));

        Utility::register('search')
            ->inertia('utilities/Search', fn () => static::searchData())
            ->title(__('Search'))
            ->icon('magnifying-glass')
            ->description(__('statamic::messages.search_utility_description'))
            ->routes(function ($router) {
                $router->post('/', [UpdateSearchController::class, 'update'])->name('update');
            });

        Utility::register('email')
            ->inertia('utilities/Email', fn () => static::emailData())
            ->title(__('Email'))
            ->icon('mail')
            ->description(__('statamic::messages.email_utility_description'))
            ->docsUrl(Statamic::docsUrl('utilities/email'))
            ->routes(function ($router) {
                $router->post('/', [EmailController::class, 'send']);
            });

        Utility::register('licensing')
            ->action([LicensingController::class, 'show'])
            ->title(__('Licensing'))
            ->icon('license')
            ->description(__('statamic::messages.licensing_utility_description'))
            ->docsUrl(Statamic::docsUrl('licensing'))
            ->routes(function ($router) {
                $router->get('refresh', [LicensingController::class, 'refresh'])->name('refresh');
            });

        if (config('statamic.git.enabled') && Statamic::pro()) {
            Utility::register('git')
                ->action([GitController::class, 'index'])
                ->title(__('Git'))
                ->icon('git')
                ->description(__('statamic::messages.git_utility_description'))
                ->docsUrl(Statamic::docsUrl('utilities/git'))
                ->routes(function ($router) {
                    $router->post('/', [GitController::class, 'commit'])->name('commit');
                });
        }
    }

    private static function searchData()
    {
        return [
            'updateUrl' => cp_route('utilities.search.update'),
            'indexes' => Search::indexes()->map(fn ($index) => [
                'name' => $index->name(),
                'locale' => $index->locale(),
                'title' => $index->title(),
                'driver' => $index->config()['driver'],
                'driverIcon' => Statamic::svg('search-drivers/'.$index->config()['driver'], '', 'search-drivers/local'),
                'searchables' => $index->config()['searchables'],
                'fields' => $index->config()['fields'],
            ])->values(),
        ];
    }

    private static function emailData()
    {
        return [
            'sendUrl' => cp_route('utilities.email'),
            'defaultEmail' => User::current()->email(),
            'config' => [
                'path' => config_path('mail.php'),
                'default' => config('mail.default'),
                'smtp' => config('mail.default') === 'smtp' ? [
                    'host' => config('mail.mailers.smtp.host'),
                    'port' => config('mail.mailers.smtp.port'),
                    'encryption' => config('mail.mailers.smtp.encryption'),
                    'username' => config('mail.mailers.smtp.username'),
                    'password' => config('mail.mailers.smtp.password'),
                ] : null,
                'sendmail' => config('mail.default') === 'sendmail' ? [
                    'path' => config('mail.mailers.sendmail.path'),
                ] : null,
                'from' => [
                    'address' => config('mail.from.address'),
                    'name' => config('mail.from.name'),
                ],
                'markdown' => [
                    'theme' => config('mail.markdown.theme'),
                    'paths' => config('mail.markdown.paths', []),
                ],
            ],
        ];
    }
}
