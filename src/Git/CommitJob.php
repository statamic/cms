<?php

namespace Statamic\Git;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Statamic\Facades\Git;

use function Statamic\trans as __;

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
        $saves = Cache::pull('statamic-git-pending-saves', []);
        $users = collect($saves)->unique('email')->values();
        $coalesced = $users->count() > 1;

        $message = $this->message;

        if ($coalesced) {
            $trailers = $users->map(fn ($u) => "Co-Authored-By: {$u['name']} <{$u['email']}>")->implode("\n");
            $message = ($message ?? __('Content saved'))."\n\n".$trailers;
        }

        Git::as($coalesced ? null : $this->committer)->commit($message);
    }
}
