<?php

namespace Statamic\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Statamic\Facades\FormSubmission;

class DeleteDraftFormSubmissions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function handle(): void
    {
        if (! ($days = config('statamic.forms.drafts.delete_after'))) {
            return;
        }

        $threshold = now()->subDays($days);

        FormSubmission::query()
            ->where('draft', true)
            ->where('date', '<', $threshold)
            ->get()
            ->each
            ->delete();
    }
}
