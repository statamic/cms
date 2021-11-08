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
     * Commit message.
     *
     * @var string|null
     */
    public $message;

    /**
     * Create a new job instance.
     *
     * @param  string|null  $message
     */
    public function __construct($message = null)
    {
        $this->message = $message;
        if ($queue = config('statamic.system.queue')) {
            $this->onQueue($queue);
        }
        if ($connection = config('statamic.system.queue_connection')) {
            $this->onConnection($connection);
        }
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        Git::commit($this->message);
    }
}
