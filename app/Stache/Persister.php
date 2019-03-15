<?php

namespace Statamic\Stache;

use Illuminate\Support\Facades\Cache;
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
        $stores = $this->stores();

        // TODO: Locking
        // In v2, we split this persistence method into two steps.
        // 1. Compile the arrays that'll be saved to the cache.
        // 2. Actually cache the items.
        // We lock the second (cache) step, but keep the first one unlocked (which is
        // doing all the work) so other requests aren't delayed. Right now, both the
        // steps are combined within `$store->cache()`. We need to split that out.
        $stores->filter->isUpdated()->each(function ($store) {
            $store->load()->cache();
        });

        $stores->filter->isExpired()->each->uncache();

        $this->stache->queuedTimestampCaches()->each(function ($timestamps, $key) {
            Cache::forever($key, $timestamps);
        });

        $this->stache->stopTimer();
    }

    protected function stores()
    {
        return $this->stache->stores()->flatMap(function ($store) {
            // We are interested in updating individual stores. eg. If a "blog" collection entry was
            // updated, we only want to update the "blog" store, and not the whole "entries" store.
            return ($store instanceof AggregateStore) ? $store->stores() : [$store];
        });
    }
}
