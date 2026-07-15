<?php

namespace Statamic\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Statamic\Contracts\Forms\Submission;
use Statamic\Facades\FormSubmission;
use Statamic\Forms\DeleteTemporaryFiles;

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
            ->where('partial', true)
            ->where('date', '<', $threshold)
            ->get()
            ->each(function (Submission $submission): void {
                DeleteTemporaryFiles::dispatchSync($submission);

                $submission->delete();
            });
    }
}
