<?php

namespace Statamic\Forms;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Statamic\Fields\Field;

/**
 * @deprecated Use Statamic\Forms\DeleteTemporaryFiles instead.
 */
class DeleteTemporaryAttachments extends DeleteTemporaryFiles
{
    public function handle(): void
    {
        $this->deleteLegacyFileUploads();

        parent::handle();
    }

    private function deleteLegacyFileUploads(): void
    {
        $submission = $this->submission->form()->submission($this->submission->id()) ?? $this->submission;
        $disk = Storage::disk(config('statamic.system.file_uploads_disk', 'local'));
        $basePath = config('statamic.system.file_uploads_path', 'statamic/file-uploads');

        $submission->form()->blueprint()->fields()->all()
            ->filter(fn (Field $field) => $field->type() === 'form_upload' && ! $field->fieldtype()->config('store'))
            ->each(fn (Field $field) => Collection::wrap($submission->get($field->handle(), []))
                ->reject(fn ($path) => str_contains($path, '..'))
                ->each(fn ($path) => $disk->delete("{$basePath}/{$path}")));
    }
}
