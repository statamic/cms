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
        $fields = $this->submission->form()->blueprint()->fields()->all();
        $disk = Storage::disk(config('statamic.system.file_uploads_disk', 'local'));
        $basePath = config('statamic.forms.file_uploads_path', 'statamic/form-uploads');

        $fields->filter(fn (Field $field) => $field->type() === 'files')->each(function (Field $field) use ($disk): void {
            $fileUploadsPath = config('statamic.system.file_uploads_path', 'statamic/file-uploads');

            Collection::wrap($this->submission->get($field->handle(), []))
                ->reject(fn ($path) => str_contains($path, '..'))
                ->each(fn ($path) => $disk->delete("{$fileUploadsPath}/".$path));
        });

        if ($disk->exists($uploadsPath = "{$basePath}/{$this->submission->id()}")) {
            $disk->deleteDirectory($uploadsPath);
        }

        $removed = $fields
            ->filter(fn (Field $field) => $field->type() === 'files' || ($field->type() === 'form_upload' && ! $field->fieldtype()->config('store')))
            ->each(fn (Field $field) => $this->submission->remove($field->handle()))
            ->isNotEmpty();

        if ($removed && $this->submission->form()->store()) {
            $this->submission->saveQuietly();
        }
    }
}
