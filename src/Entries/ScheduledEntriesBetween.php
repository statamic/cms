<?php

namespace Statamic\Entries;

use Carbon\CarbonInterface;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;

class ScheduledEntriesBetween
{
    /**
     * Entries in dated collections with a date from $from (inclusive) to $until (exclusive).
     */
    public function __construct(
        private readonly CarbonInterface $from,
        private readonly CarbonInterface $until
    ) {
    }

    public function __invoke(): \Illuminate\Support\Collection
    {
        return Entry::query()
            ->whereIn('collection', Collection::all()->filter->dated()->map->handle()->all())
            ->where('date', '>=', $this->from)
            ->where('date', '<', $this->until)
            ->get();
    }
}
