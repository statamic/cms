<?php

namespace Statamic\Events;

abstract class Subscriber
{
    protected $listeners = [];

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        foreach ($this->getListeners() as $event => $listener) {
            $events->listen($event, $listener);
        }
    }

    /**
     * Get subscribable listeners.
     *
     * @return array
     */
    protected function getListeners()
    {
        if (! $this->listeners) {
            throw new \Exception('No event listeners registered in [$listeners] property!');
        }

        return $this->listeners;
    }

    /**
     * Temporarily disable the listeners handled by this subscriber.
     */
    public static function disable()
    {
        foreach ((new static)->getListeners() as $event => $listener) {
            app('events')->forgetListener($event, $listener);
        }
    }

    /**
     * Re-enable the listeners handled by this subscriber.
     */
    public static function enable()
    {
        (new static)->subscribe(app('events'));
    }
}
