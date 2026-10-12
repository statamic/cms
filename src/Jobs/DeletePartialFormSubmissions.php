<?php

namespace Statamic\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Statamic\Facades\FormSubmission;

class DeletePartialFormSubmissions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function handle(): void
    {
        if (! ($days = config('statamic.forms.delete_partial_submissions_after'))) {
            return;
        }

        $threshold = now()->subDays($days);

        FormSubmission::query()
            ->whereStatus('partial')
            ->where('date', '<', $threshold)
            ->get()
            ->each->delete();
    }
}
