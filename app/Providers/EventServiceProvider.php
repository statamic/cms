<?php

namespace Statamic\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        \Statamic\Events\DataIdCreated::class => [
            \Statamic\Stache\Listeners\SaveCreatedId::class
        ]
    ];

    protected $subscribe = [
        \Statamic\Stache\Listeners\UpdateItem::class,
        \Statamic\Data\Taxonomies\TermTracker::class,
        \Statamic\Listeners\GeneratePresetImageManipulations::class,
        \Statamic\StaticCaching\Invalidator::class,
        \Statamic\Listeners\UpdateRoutes::class,
    ];
}
