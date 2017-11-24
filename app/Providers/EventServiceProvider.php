<?php

namespace Statamic\Providers;

use Statamic\Extend\Management\AddonRepository;
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

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        $dispatcher = $this->app->make('events');

        // Register all the events specified in each listener class
        foreach ($this->app->make(AddonRepository::class)->listeners()->installed()->classes() as $class) {
            $listener = app($class);

            foreach ($listener->events as $event => $methods) {
                foreach (Helper::ensureArray($methods) as $method) {
                    $dispatcher->listen($event, [$listener, $method]);
                }
            }
        }
    }
}
