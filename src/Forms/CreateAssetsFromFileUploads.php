<?php

namespace Statamic\Forms;

use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Statamic\Contracts\Forms\Submission;
use Statamic\Facades\Asset;
use Statamic\Fields\Field;
use Statamic\Forms\Uploaders\AssetsUploader;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CreateAssetsFromFileUploads
{
    use Dispatchable, SerializesModels;

    public function __construct(public Submission $submission)
    {
    }

    public function handle(): void
    {
        $created = $this->submission->form()->blueprint()->fields()->all()
            ->filter(fn (Field $field): bool => $field->type() === 'form_upload' && $field->fieldtype()->config('store'))
            ->reduce(function (bool $created, Field $field): bool {
                $paths = $this->createAssets($field);

                if (! $paths) {
                    return $created;
                }

                $this->submission->set($field->handle(), $paths);

                return true;
            }, false);

        if ($created && $this->submission->form()->store()) {
            $this->submission->saveQuietly();
        }
    }

    private function createAssets(Field $field): array|string|null
    {
        $value = $this->submission->get($field->handle());

        if (! $value) {
            return null;
        }

        $assetPaths = Collection::wrap($value)
            ->filter()
            ->map(fn (string $path) => $this->uploadAsset($field, $path))
            ->filter()
            ->values();

        if ($assetPaths->isEmpty()) {
            return null;
        }

        return $field->get('max_files') === 1 ? $assetPaths->first() : $assetPaths->all();
    }

    private function uploadAsset(Field $field, string $path): ?string
    {
        $disk = Storage::disk(config('statamic.system.file_uploads_disk', 'local'));
        $basePath = config('statamic.forms.file_uploads_path', 'statamic/form-uploads');
        $diskPath = "{$basePath}/{$this->submission->id()}/{$field->handle()}/".basename($path);

        if (! $disk->exists($diskPath)) {
            return null;
        }

        $localPath = tempnam(sys_get_temp_dir(), 'statamic-form-upload');
        $stream = $disk->readStream($diskPath);
        file_put_contents($localPath, $stream);
        if (is_resource($stream)) {
            fclose($stream);
        }

        $uploadedFile = new UploadedFile(
            $localPath,
            basename($path),
            $disk->mimeType($diskPath),
            null,
            true
        );

        $assetId = AssetsUploader::field($field->toArray())->upload($uploadedFile);
        $assetId = is_array($assetId) ? $assetId[0] : $assetId;

        app('files')->delete($localPath);
        $disk->delete($diskPath);

        return Asset::findOrFail($assetId)->path();
    }
}
