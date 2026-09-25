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
        $submission = $this->submission->form()->submission($this->submission->id()) ?? $this->submission;

        $fields = $submission->form()->blueprint()->fields()->all();
        $disk = Storage::disk(config('statamic.system.file_uploads_disk', 'local'));
        $basePath = config('statamic.forms.file_uploads_path', 'statamic/form-uploads');

        $fields->filter(fn (Field $field) => $field->type() === 'files')->each(function (Field $field) use ($disk, $submission): void {
            $fileUploadsPath = config('statamic.system.file_uploads_path', 'statamic/file-uploads');

            Collection::wrap($submission->get($field->handle(), []))
                ->reject(fn ($path) => str_contains($path, '..'))
                ->each(fn ($path) => $disk->delete("{$fileUploadsPath}/".$path));
        });

        if ($disk->exists($uploadsPath = "{$basePath}/{$submission->id()}")) {
            $disk->deleteDirectory($uploadsPath);
        }

        $removed = $fields
            ->filter(fn (Field $field) => $field->type() === 'files' || ($field->type() === 'form_upload' && ! $field->fieldtype()->config('store')))
            ->each(fn (Field $field) => $submission->remove($field->handle()))
            ->isNotEmpty();

        if ($removed && $submission->form()->store()) {
            $submission->saveQuietly();
        }
    }
}
