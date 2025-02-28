<?php

namespace Statamic\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Statamic\Entries\MinuteRevisions;
use Statamic\Events\EntryScheduleReached;
use Statamic\Revisions\Revision;

class HandleRevisionSchedule implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function handle()
    {
        $this
            ->revisions()
            ->each(fn (Revision $revision) => EntryScheduleReached::dispatch(
                tap($revision->entry()->makeFromRevision($revision))->save()
            ));
    }

    private function revisions(): Collection
    {
        // We want to target the PREVIOUS minute because we can be sure that any entries that
        // were scheduled for then would now be considered published. If we were targeting
        // the current minute and the entry has defined a time with seconds later in the
        // same minute, it may still be considered scheduled when it gets dispatched.
        $minute = now()->subMinute();

        return (new MinuteRevisions($minute))();
    }
}
