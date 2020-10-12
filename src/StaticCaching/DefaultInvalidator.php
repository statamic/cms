<?php

namespace Statamic\StaticCaching;

use Statamic\Contracts\Entries\Entry;
use Statamic\Contracts\Globals\GlobalSet;
use Statamic\Contracts\Structures\Nav;
use Statamic\Contracts\Taxonomies\Term;
use Statamic\Support\Arr;

class DefaultInvalidator implements Invalidator
{
    protected $cacher;
    protected $rules;

    public function __construct(Cacher $cacher, $rules = [])
    {
        $this->cacher = $cacher;
        $this->rules = $rules;
    }

    public function invalidate($item)
    {
        if ($this->rules === 'all') {
            return $this->cacher->flush();
        }

        if ($item instanceof Entry) {
            // Invalidate the item's own URL.
            if ($url = $item->url()) {
                $this->cacher->invalidateUrl($url);
            }

            $this->invalidateEntryUrls($item);
        } elseif ($item instanceof Term) {
            // Invalidate the item's own URL.
            if ($url = $item->url()) {
                $this->cacher->invalidateUrl($url);
            }

            $this->invalidateTermUrls($item);
        } elseif ($item instanceof GlobalSet) {
            $this->cacher->invalidateUrls($this->cacher->getUrls());
        } elseif ($item instanceof Nav) {
            $this->cacher->invalidateUrls($this->cacher->getUrls());
        }
    }

    protected function invalidateEntryUrls($entry)
    {
        $collection = $entry->collectionHandle();

        $this->cacher->invalidateUrls(
            Arr::get($this->rules, "collections.$collection.urls")
        );
    }

    protected function invalidateTermUrls($term)
    {
        $taxonomy = $term->taxonomyHandle();

        $this->cacher->invalidateUrls(
            Arr::get($this->rules, "taxonomies.$taxonomy.urls")
        );
    }
}
