<?php

namespace Statamic\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Statamic\Events\Subscriber;
use Statamic\Events\TermDeleted;
use Statamic\Events\TermReferencesUpdated;
use Statamic\Events\TermSaved;
use Statamic\Taxonomies\TermReferenceUpdater;

class UpdateTermReferences extends Subscriber implements ShouldQueue
{
    use Concerns\GetsItemsContainingData;

    protected $listeners = [
        TermSaved::class => 'handleSaved',
        TermDeleted::class => 'handleDeleted',
    ];

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

        parent::subscribe($events);
    }

    /**
     * Handle the term saved event.
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

        $hasUpdatedItems = false;

        $this
            ->getItemsContainingData()
            ->each(function ($item) use ($taxonomy, $originalSlug, $newSlug, &$hasUpdatedItems) {
                $updated = TermReferenceUpdater::item($item)
                    ->filterByTaxonomy($taxonomy)
                    ->updateReferences($originalSlug, $newSlug);

                if ($updated) {
                    $hasUpdatedItems = true;
                }

                if (method_exists($item, 'hasWorkingCopy') && $item->hasWorkingCopy()) {
                    $workingCopyEntry = $item->makeFromRevision($item->workingCopy());

                    $workingCopyUpdated = TermReferenceUpdater::item($workingCopyEntry)
                        ->filterByTaxonomy($taxonomy)
                        ->withoutSaving()
                        ->updateReferences($originalSlug, $newSlug);

                    if ($workingCopyUpdated) {
                        $workingCopyEntry->saveToWorkingCopy();
                    }
                }
            });

        if ($hasUpdatedItems) {
            TermReferencesUpdated::dispatch($term);
        }
    }
}
