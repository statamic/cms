<?php

namespace Statamic\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Statamic\Contracts\Forms\Submission;
use Statamic\Facades\Asset;
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
            ->where('partial', true)
            ->where('date', '<', $threshold)
            ->get()
            ->each(function (Submission $submission): void {
                if (config('statamic.forms.garbage_collect_assets')) {
                    $this->garbageCollectAssets($submission);
                }

                $submission->delete();
            });
    }

    private function garbageCollectAssets(Submission $submission): void
    {
        $submission->form()->blueprint()->fields()->all()
            ->filter(fn ($field) => $field->fieldtype()->handle() === 'assets'
                || ($field->fieldtype()->handle() === 'form_upload' && $field->fieldtype()->config('store')))
            ->each(function ($field) use ($submission) {
                $container = $field->get('container');

                collect($submission->get($field->handle()))
                    ->filter()
                    ->each(fn ($path) => Asset::find("{$container}::{$path}")?->delete());
            });
    }
}
