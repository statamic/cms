<?php

namespace Statamic\Git;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Statamic\Facades\Git;

class CommitJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $uniqueFor;

    /**
     * Create a new job instance.
     */
    public function __construct(public $message = null, public $committer = null)
    {
        $this->uniqueFor = config('statamic.git.unique_lock_expiry', 120);
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $committer = Cache::pull('statamic-git-pending-saves', 0) > 1 ? null : $this->committer;

        Git::as($committer)->commit($this->message);
    }
}
