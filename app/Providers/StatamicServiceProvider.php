<?php

namespace Statamic\Providers;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\AggregateServiceProvider;

class StatamicServiceProvider extends AggregateServiceProvider
{
    /**
     * The provider class names.
     *
     * @var array
     */
    protected $providers = [
        ViewServiceProvider::class,
        AppServiceProvider::class,
        ConsoleServiceProvider::class,
        CollectionsServiceProvider::class,
        DataServiceProvider::class,
        FilesystemServiceProvider::class,
        ExtensionServiceProvider::class,
        EventServiceProvider::class,
        \Statamic\Stache\ServiceProvider::class,
        AuthServiceProvider::class,
        GlideServiceProvider::class,
        \Statamic\StaticCaching\ServiceProvider::class,
        CpServiceProvider::class,
        ValidationServiceProvider::class,

        // AuthServiceProvider::class,
        // BroadcastServiceProvider::class,
        // EventServiceProvider::class,
    ];
}
