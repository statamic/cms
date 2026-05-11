<?php

namespace Statamic\Forms;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Statamic\Contracts\Forms\Submission;
use Statamic\Fields\Field;

class DeleteTemporaryAttachments implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Submission $submission)
    {
    }

    public function handle()
    {
        $this->submission->form()->blueprint()->fields()->all()
            ->filter(fn (Field $field) => $field->type() === 'files')
            ->each(function (Field $field) {
                Collection::wrap($this->submission->get($field->handle(), []))
                    ->each(fn ($path) => Storage::disk('local')->delete('statamic/file-uploads/'.$path));

                $this->submission->remove($field->handle());
            });

        if ($this->submission->form()->store()) {
            $this->submission->saveQuietly();
        }
    }
}
