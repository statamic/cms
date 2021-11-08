<?php

namespace Statamic\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Statamic\Events\TermSaved;
use Statamic\Taxonomies\TermReferenceUpdater;

class UpdateTermReferences implements ShouldQueue
{
    use Concerns\GetsItemsContainingData;

    /**
     * Get the name of the listener's queue connection.
     *
     * @return string
     */
    public function viaConnection()
    {
        if ($connection = config('statamic.system.queue_connection')) {
            return $connection;
        }
        return config('queue.default');
    }

    /**
     * Get the name of the listener's queue.
     *
     * @return string
     */
    public function viaQueue()
    {
        if ($queue = config('statamic.system.queue')) {
            return $queue;
        }
        return config('queue.connections.'. $this->viaConnection().'.queue');
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(TermSaved::class, self::class.'@handle');
    }

    /**
     * Handle the events.
     *
     * @param  TermSaved  $event
     */
    public function handle(TermSaved $event)
    {
        $term = $event->term;

        $taxonomy = $term->taxonomy()->handle();
        $originalSlug = $term->getOriginal('slug');
        $newSlug = $term->slug();

        if (! $originalSlug || $originalSlug === $newSlug) {
            return;
        }

        $this->getItemsContainingData()->each(function ($item) use ($taxonomy, $originalSlug, $newSlug) {
            TermReferenceUpdater::item($item)
                ->filterByTaxonomy($taxonomy)
                ->updateReferences($originalSlug, $newSlug);
        });
    }
}
