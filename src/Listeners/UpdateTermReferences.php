<?php

namespace Statamic\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Statamic\Events\TermDeleted;
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

        $taxonomy = $term->taxonomy()->handle();
        $originalSlug = $term->getOriginal('slug');
        $newSlug = $term->slug();

        $this->replaceReferences($taxonomy, $originalSlug, $newSlug);
    }

    /**
     * Handle the term deleted event.
     *
     * @param  TermDeleted  $event
     */
    public function handleDeleted(TermDeleted $event)
    {
        $term = $event->term;

        $taxonomy = $term->taxonomy()->handle();
        $originalSlug = $term->getOriginal('slug');
        $newSlug = null;

        $this->replaceReferences($taxonomy, $originalSlug, $newSlug);
    }

    /**
     * Replace term references.
     *
     * @param  string  $taxonomy
     * @param  string  $originalSlug
     * @param  string  $newSlug
     */
    protected function replaceReferences($taxonomy, $originalSlug, $newSlug)
    {
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
