<?php

namespace Statamic\Stache;

use Statamic\Stache\Stores\AggregateStore;

class Persister
{
    protected $stache;

    public function __construct(Stache $stache)
    {
        $this->stache = $stache;
    }

    public function persist()
    {
        // TODO: Locking
        // In v2, we split this persistence method into two steps.
        // 1. Compile the arrays that'll be saved to the cache.
        // 2. Actually cache the items.
        // We lock the second (cache) step, but keep the first one unlocked (which is
        // doing all the work) so other requests aren't delayed. Right now, both the
        // steps are combined within `$store->cache()`. We need to split that out.
        $this->updatedStores()->each(function ($store) {
            $store->load()->cache();
        });

        $this->stache->stopTimer();
    }

    protected function updatedStores()
    {
        return $this->stache->stores()->flatMap(function ($store) {
            // We are interested in updating individual stores. eg. If a "blog" collection entry was
            // updated, we only want to update the "blog" store, and not the whole "entries" store.
            return ($store instanceof AggregateStore) ? $store->stores() : [$store];
        })->filter->isUpdated();
    }
}
