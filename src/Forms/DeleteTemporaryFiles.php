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

class DeleteTemporaryFiles implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Submission $submission)
    {
    }

    public function handle(): void
    {
        $uploadFields = $this->submission->form()->blueprint()->fields()->all()
            ->filter(fn (Field $field) => $field->type() === 'files' || ($field->type() === 'form_upload' && ! $field->fieldtype()->config('store')));

        if ($uploadFields->isEmpty()) {
            return;
        }

        $uploadFields->filter(fn (Field $field) => $field->type() === 'files')->each(function (Field $field): void {
            Collection::wrap($this->submission->get($field->handle(), []))
                ->reject(fn ($path) => str_contains($path, '..'))
                ->each(fn ($path) => Storage::disk('local')->delete('statamic/file-uploads/'.$path));
        });

        if (Storage::disk('local')->exists($uploadsPath = "statamic/form-uploads/{$this->submission->id()}")) {
            Storage::disk('local')->deleteDirectory($uploadsPath);
        }

        $uploadFields->each(fn (Field $field) => $this->submission->remove($field->handle()));

        if ($this->submission->form()->store()) {
            $this->submission->saveQuietly();
        }
    }
}
