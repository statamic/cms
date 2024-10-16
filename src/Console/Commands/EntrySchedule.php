<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;
use Statamic\Events\EntryScheduleFulfilled;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;

class EntrySchedule extends Command
{
    use RunsInPlease;

    protected $signature = 'statamic:entry:schedule';

    public function handle()
    {
        $this->entries()->each(fn ($entry) => EntryScheduleFulfilled::dispatch($entry));
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
