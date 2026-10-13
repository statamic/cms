<?php

namespace Statamic\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Statamic\Facades\FormSubmission;

class DeleteSpamFormSubmissions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function handle(): void
    {
        if (! ($days = config('statamic.forms.delete_spam_submissions_after'))) {
            return;
        }

        $threshold = now()->subDays($days)->timestamp;

        FormSubmission::query()
            ->whereStatus('spam')
            ->where('_marked_spam_at', '<', $threshold)
            ->get()
            ->each->delete();
    }
}
