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

    /**
     * The number of seconds after which the job's unique lock will be released.
     */
    public int $uniqueFor = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(public $message = null, public $committer = null)
    {
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
