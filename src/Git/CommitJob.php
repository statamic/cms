<?php

namespace Statamic\Git;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Statamic\Facades\Git;

class CommitJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

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
        Git::as($this->committer)->commit($this->message);
    }
}
