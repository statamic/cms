<?php

namespace Statamic\Providers;

use Illuminate\Support\AggregateServiceProvider;

class StatamicServiceProvider extends AggregateServiceProvider
{
    /**
     * The provider class names.
     *
     * @var array
     */
    protected $providers = [
        \Illuminate\Auth\AuthServiceProvider::class,
        \Illuminate\Broadcasting\BroadcastServiceProvider::class,
        \Illuminate\Bus\BusServiceProvider::class,
        \Illuminate\Cache\CacheServiceProvider::class,
        \Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
        \Illuminate\Cookie\CookieServiceProvider::class,
        \Illuminate\Database\DatabaseServiceProvider::class,
        \Illuminate\Encryption\EncryptionServiceProvider::class,
        \Illuminate\Filesystem\FilesystemServiceProvider::class,
        \Illuminate\Foundation\Providers\FoundationServiceProvider::class,
        \Illuminate\Hashing\HashServiceProvider::class,
        \Illuminate\Mail\MailServiceProvider::class,
        \Illuminate\Notifications\NotificationServiceProvider::class,
        \Illuminate\Pagination\PaginationServiceProvider::class,
        \Illuminate\Pipeline\PipelineServiceProvider::class,
        \Illuminate\Queue\QueueServiceProvider::class,
        \Illuminate\Redis\RedisServiceProvider::class,
        \Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
        \Illuminate\Session\SessionServiceProvider::class,
        \Illuminate\Translation\TranslationServiceProvider::class,
        \Illuminate\Validation\ValidationServiceProvider::class,

        ViewServiceProvider::class,
        AppServiceProvider::class,
        CollectionsServiceProvider::class,
        DataServiceProvider::class,
        FilesystemServiceProvider::class,
        EventServiceProvider::class,
        RouteServiceProvider::class,
        StacheServiceProvider::class,
        AuthServiceProvider::class,
        GlideServiceProvider::class,
        \Statamic\StaticCaching\ServiceProvider::class,
        CpServiceProvider::class,

        \Collective\Html\HtmlServiceProvider::class,

        // AuthServiceProvider::class,
        // BroadcastServiceProvider::class,
        // EventServiceProvider::class,
    ];
}
