<?php

namespace Statamic\Forms;

use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Statamic\Contracts\Assets\Asset as AssetContract;
use Statamic\Contracts\Forms\Submission;
use Statamic\Facades\Asset;
use Statamic\Fields\Field;
use Statamic\Forms\Uploaders\AssetsUploader;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CreateAssetsFromFileUploads
{
    use Dispatchable, SerializesModels;

    private array $created = [];
    private array $assets = [];
    private array $temporaryFiles = [];

    public function __construct(public Submission $submission, public bool $keepTemporaryFiles = false)
    {
    }

    public function handle(): void
    {
        $this->submission->form()->blueprint()->fields()->all()
            ->filter(fn (Field $field): bool => $field->type() === 'form_upload' && $field->fieldtype()->config('store'))
            ->each(function (Field $field): void {
                $original = $this->submission->get($field->handle());

                if ($paths = $this->createAssets($field)) {
                    $this->created[$field->handle()] = $original;
                    $this->submission->set($field->handle(), $paths);
                }
            });
    }

    public function revert(): void
    {
        foreach ($this->created as $handle => $original) {
            $this->assets[$handle]->each->delete();
            $this->submission->set($handle, $original);
        }

        $this->created = [];
        $this->assets = [];
    }

    public function deleteTemporaryFiles(): void
    {
        foreach ($this->temporaryFiles as $path) {
            $this->disk()->delete($path);
        }

        $this->temporaryFiles = [];
    }

    private function createAssets(Field $field): array|string|null
    {
        $value = $this->submission->get($field->handle());

        if (! $value) {
            return null;
        }

        $assets = Collection::wrap($value)
            ->filter()
            ->map(fn (string $path) => $this->uploadAsset($field, $path))
            ->filter()
            ->values();

        if ($assets->isEmpty()) {
            return null;
        }

        $this->assets[$field->handle()] = $assets;

        $assetPaths = $assets->map->path();

        return $field->get('max_files') === 1 ? $assetPaths->first() : $assetPaths->all();
    }

    private function uploadAsset(Field $field, string $path): ?AssetContract
    {
        $disk = $this->disk();
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

        if ($this->keepTemporaryFiles) {
            $this->temporaryFiles[] = $diskPath;
        } else {
            $disk->delete($diskPath);
        }

        return Asset::findOrFail($assetId);
    }

    private function disk()
    {
        return Storage::disk(config('statamic.system.file_uploads_disk', 'local'));
    }
}
