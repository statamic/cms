<?php

namespace Statamic\Entries;

use Carbon\CarbonInterface;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;

class MinuteEntries
{
    public function __construct(private readonly CarbonInterface $minute)
    {
    }

    public function __invoke(): \Illuminate\Support\Collection
    {
        return Entry::query()
            ->whereIn('collection', Collection::all()->filter->dated()->map->handle()->all())
            ->whereDate('date', $this->minute->format('Y-m-d'))
            ->where(function ($query) {
                $query->where(function ($query) {
                    $query
                        ->whereTime('date', '>=', $this->minute->format('H:i').':00')
                        ->whereTime('date', '<=', $this->minute->format('H:i').':59');
                });
            })->get();
    }
}
