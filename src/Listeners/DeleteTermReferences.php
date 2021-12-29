<?php

namespace Statamic\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Statamic\Events\TermDeleted;
use Statamic\Taxonomies\TermReferenceUpdater;

class DeleteTermReferences implements ShouldQueue
{
    use Concerns\GetsItemsContainingData;

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(TermDeleted::class, self::class.'@handle');
    }

    /**
     * Handle the events.
     *
     * @param  TermDeleted  $event
     */
    public function handle(TermDeleted $event)
    {
        $term = $event->term;

        $taxonomy = $term->taxonomy()->handle();
        $slug = $term->slug();

        $this->getItemsContainingData()->each(function ($item) use ($taxonomy, $slug) {
            TermReferenceUpdater::item($item)
                ->filterByTaxonomy($taxonomy)
                ->updateReferences($slug, null);
        });
    }
}
