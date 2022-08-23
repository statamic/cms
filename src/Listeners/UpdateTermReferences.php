<?php

namespace Statamic\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Statamic\Events\TermDeleted;
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
        if (config('statamic.system.update_references') === false) {
            return;
        }

        $events->listen(TermSaved::class, self::class.'@handleSaved');
        $events->listen(TermDeleted::class, self::class.'@handleDeleted');
    }

    /**
     * Handle the term saved event.
     *
     * @param  TermSaved  $event
     */
    public function handleSaved(TermSaved $event)
    {
        $term = $event->term;
        $originalSlug = $term->getOriginal('slug');
        $newSlug = $term->slug();

        $this->replaceReferences($term, $originalSlug, $newSlug);
    }

    /**
     * Handle the term deleted event.
     *
     * @param  TermDeleted  $event
     */
    public function handleDeleted(TermDeleted $event)
    {
        $term = $event->term;
        $originalSlug = $term->getOriginal('slug');
        $newSlug = null;

        $this->replaceReferences($term, $originalSlug, $newSlug);
    }

    /**
     * Replace term references.
     *
     * @param  \Statamic\Taxonomies\Term  $term
     * @param  string  $originalSlug
     * @param  string  $newSlug
     */
    protected function replaceReferences($term, $originalSlug, $newSlug)
    {
        if (! $originalSlug || $originalSlug === $newSlug) {
            return;
        }

        $taxonomy = $term->taxonomy()->handle();

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
