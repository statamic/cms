<?php

namespace Statamic\Listeners;

use Statamic\Events\SiteDeleted;
use Statamic\Events\Subscriber;
use Statamic\Facades\Blink;
use Statamic\Facades\Collection;
use Statamic\Facades\GlobalSet;
use Statamic\Facades\Nav;
use Statamic\Facades\Taxonomy;
use Statamic\Taxonomies\LocalizedTerm;

class CleanupSiteContent extends Subscriber
{
    protected $listeners = [
        SiteDeleted::class => 'handle',
    ];

    public function handle(SiteDeleted $event)
    {
        $site = $event->site->handle();

        $this->cleanupCollections($site);
        $this->cleanupTaxonomies($site);
        $this->cleanupNavigations($site);
        $this->cleanupGlobals($site);
    }

    /**
     * Delete a collection's entries and tree for the site, then drop the site
     * from the collection's config so it stops being referenced.
     */
    private function cleanupCollections(string $site)
    {
        Collection::all()
            ->filter(fn ($collection) => $collection->sites()->contains($site))
            ->each(function ($collection) use ($site) {
                $collection->queryEntries()->where('site', $site)->get()->each(function ($entry) {
                    // Re-root any localizations in surviving sites before removing their origin.
                    $entry->detachLocalizations();
                    $entry->delete();
                });

                if ($collection->hasStructure()) {
                    $collection->structure()->in($site)?->delete();
                }

                $remaining = $collection->sites()
                    ->reject(fn ($handle) => $handle === $site)
                    ->values()
                    ->all();

                $collection->sites($remaining)->save();
            });
    }

    /**
     * Drop the site from each taxonomy's config, then strip that site's data
     * from every term (deleting terms that are left with no localizations).
     */
    private function cleanupTaxonomies(string $site)
    {
        Taxonomy::all()
            ->filter(fn ($taxonomy) => $taxonomy->sites()->contains($site))
            ->each(function ($taxonomy) use ($site) {
                $remaining = $taxonomy->sites()
                    ->reject(fn ($handle) => $handle === $site)
                    ->values()
                    ->all();

                $taxonomy->sites($remaining)->save();

                // The term's default locale is derived from the taxonomy's sites,
                // so make sure a stale copy isn't used when re-saving terms below.
                Blink::forget("taxonomy-{$taxonomy->handle()}");

                $sites = $taxonomy->sites();

                $taxonomy->queryTerms()->get()->each(function ($term) use ($site, $sites) {
                    // Term queries yield localized terms; operate on the base term.
                    $term = $term instanceof LocalizedTerm ? $term->term() : $term;

                    $term->removeLocalization($site);

                    $surviving = $sites->filter(fn ($handle) => $term->hasLocalization($handle));

                    if ($surviving->isEmpty()) {
                        $term->delete();

                        return;
                    }

                    // The term is written with the taxonomy's first site as its root
                    // locale. If the deleted site was that root, promote a surviving
                    // localization so the term still has valid root data.
                    if (! $term->hasLocalization($default = $sites->first())) {
                        $term->dataForLocale($default, $term->dataForLocale($surviving->first())->all());
                    }

                    $term->save();
                });
            });
    }

    /**
     * Delete each navigation's tree for the site. A nav's available sites are
     * derived from its trees, so nothing else needs to change.
     */
    private function cleanupNavigations(string $site)
    {
        Nav::all()->each(fn ($nav) => $nav->in($site)?->delete());
    }

    /**
     * Drop the site from each global set's config, null out any origins that
     * pointed at it, and let the set delete the orphaned localization on save.
     */
    private function cleanupGlobals(string $site)
    {
        GlobalSet::all()
            ->filter(fn ($set) => $set->sites()->contains($site))
            ->each(function ($set) use ($site) {
                $origins = $set->origins()
                    ->reject(fn ($origin, $handle) => $handle === $site)
                    ->map(fn ($origin) => $origin === $site ? null : $origin)
                    ->all();

                $set->sites($origins)->save();
            });
    }
}
