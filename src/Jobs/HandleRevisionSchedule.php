<?php

namespace Statamic\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Statamic\Contracts\Entries\Entry;
use Statamic\Contracts\Revisions\Revision;
use Statamic\Events\EntryScheduleReached;
use Statamic\Facades\Entry as Entries;
use Statamic\Facades\Revision as Revisions;

class HandleRevisionSchedule implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function handle()
    {
        $this
            ->dueRevisions()
            ->map(fn (Revision $revision) => Entries::find($revision->attribute('id'))?->publishRevision($revision))
            ->filter()
            ->each(fn (Entry $entry) => EntryScheduleReached::dispatch($entry));
    }

    private function dueRevisions(): Collection
    {
        return Revisions::query()
            ->where('action', '!=', 'working')
            ->whereNotNull('publish_at')
            ->where('publish_at', '<=', now())
            ->get()
            ->sortBy->publishAt();
    }
}
