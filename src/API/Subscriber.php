<?php

namespace Statamic\API;

use Statamic\Events\Concerns\ListensForContentEvents;

class Subscriber
{
    use ListensForContentEvents;

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        foreach ($this->events as $event) {
            $events->listen($event, self::class.'@invalidate');
        }
    }

    /**
     * Invalidate API cache.
     *
     * @param  mixed  $event
     */
    public function invalidate($event)
    {
        if ($this->eventIsIgnored($event)) {
            return;
        }

        app(Cacher::class)->handleInvalidationEvent($event);
    }

    /**
     * Check if event is ignored.
     *
     * @param  mixed  $event
     * @return bool
     */
    protected function eventIsIgnored($event)
    {
        return collect(config('statamic.api.cache.ignored_events'))->contains(get_class($event));
    }
}
