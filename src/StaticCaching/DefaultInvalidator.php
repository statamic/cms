<?php

namespace Statamic\StaticCaching;

use Statamic\Contracts\Entries\Entry;
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

        // Invalidate the item's own URL.
        if ($url = $item->url()) {
            $this->cacher->invalidateUrl($url);
        }

        if ($item instanceof Entry) {
            $this->invalidateEntryUrls($item);
        } elseif ($item instanceof Term) {
            $this->invalidateTermUrls($item);
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
