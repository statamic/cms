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
        AppServiceProvider::class,
        DataServiceProvider::class,
        EventServiceProvider::class,
        RouteServiceProvider::class,
        GlideServiceProvider::class,
        \Statamic\StaticCaching\ServiceProvider::class,

        // AuthServiceProvider::class,
        // BroadcastServiceProvider::class,
        // EventServiceProvider::class,
    ];
}
