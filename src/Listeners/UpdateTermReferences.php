<?php

namespace Statamic\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Statamic\Events\TermReferencesUpdated;
use Statamic\Events\TermSaved;
use Statamic\Taxonomies\TermReferenceUpdater;

class UpdateTermReferences implements ShouldQueue
{
    use Concerns\GetsItemsContainingData;

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

        $updatedItems = $this
            ->getItemsContainingData()
            ->map(function ($item) use ($taxonomy, $originalSlug, $newSlug) {
                return TermReferenceUpdater::item($item)
                    ->filterByTaxonomy($taxonomy)
                    ->updateReferences($originalSlug, $newSlug);
            })
            ->filter();

        if ($updatedItems->isNotEmpty()) {
            TermReferencesUpdated::dispatch($term);
        }
    }
}
