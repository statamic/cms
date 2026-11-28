<?php

namespace Statamic\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Statamic\Contracts\Revisions\Revision;
use Statamic\Facades\Entry as Entries;
use Statamic\Facades\Revision as Revisions;

class HandleRevisionSchedule implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function handle()
    {
        $this
            ->dueRevisions()
            ->each(fn (Revision $revision) => Entries::find($revision->attribute('id'))?->publishRevision($revision));
    }

    private function dueRevisions(): Collection
    {
        return Revisions::query()
            ->whereNotNull('publish_at')
            ->where('publish_at', '<=', now())
            ->get()
            ->sortBy->publishAt();
    }
}
