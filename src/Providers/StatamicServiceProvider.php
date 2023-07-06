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
        IgnitionServiceProvider::class,
        ViewServiceProvider::class,
        CacheServiceProvider::class,
        AppServiceProvider::class,
        ConsoleServiceProvider::class,
        CollectionsServiceProvider::class,
        FilesystemServiceProvider::class,
        ExtensionServiceProvider::class,
        EventServiceProvider::class,
        \Statamic\Stache\ServiceProvider::class,
        AuthServiceProvider::class,
        GlideServiceProvider::class,
        MarkdownServiceProvider::class,
        \Statamic\Search\ServiceProvider::class,
        \Statamic\StaticCaching\ServiceProvider::class,
        \Statamic\Revisions\ServiceProvider::class,
        CpServiceProvider::class,
        ValidationServiceProvider::class,
        RouteServiceProvider::class,
        BroadcastServiceProvider::class,
        \Statamic\API\ServiceProvider::class,
        \Statamic\Git\ServiceProvider::class,
        \Statamic\GraphQL\ServiceProvider::class,
        BardServiceProvider::class,
        \Statamic\Preferences\ServiceProvider::class,
    ];
}
