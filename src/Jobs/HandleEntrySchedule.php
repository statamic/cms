<?php

namespace Statamic\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Statamic\Events\EntryScheduleReached;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;

class HandleEntrySchedule implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function handle()
    {
        $this->entries()->each(fn ($entry) => EntryScheduleReached::dispatch($entry));
    }

    private function entries()
    {
        $now = now()->startOfMinute();
        $field = 'date';

        return Entry::query()
            ->whereIn('collection', Collection::all()->filter->dated()->map->handle()->all())
            ->whereDate($field, $now->format('Y-m-d'))
            ->where(function ($query) use ($now, $field) {
                $query->where(function ($query) use ($field, $now) {
                    $query
                        ->whereTime($field, '>=', $now->format('H:i').':00')
                        ->whereTime($field, '<=', $now->format('H:i').':59');
                })->orWhereTime($field, '=', $now->format('H:i'));
            })
            ->get();
    }
}
