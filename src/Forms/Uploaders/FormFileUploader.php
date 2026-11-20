<?php

namespace Statamic\Forms\Uploaders;

use Statamic\Assets\FileUploader;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FormFileUploader extends FileUploader
{
    protected string $submissionId;

    protected string $handle;

    public static function submission(string $submissionId, string $handle, ?string $container = null): static
    {
        $uploader = new static($container);
        $uploader->submissionId = $submissionId;
        $uploader->handle = $handle;

        return $uploader;
    }

    protected function uploadPath(UploadedFile $file)
    {
        $directory = "{$this->submissionId}/{$this->handle}";

        return "{$directory}/".$this->uniqueFilename($directory, $file->getClientOriginalName());
    }

    private function uniqueFilename(string $directory, string $filename, int $count = 0): string
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $basename = pathinfo($filename, PATHINFO_FILENAME);
        $suffix = $count ? "-{$count}" : '';
        $candidate = $basename.$suffix.($extension ? ".{$extension}" : '');

        if ($this->disk()->exists($this->uploadPathPrefix()."{$directory}/{$candidate}")) {
            return $this->uniqueFilename($directory, $filename, $count + 1);
        }

        return $candidate;
    }

    protected function uploadPathPrefix()
    {
        return config('statamic.forms.file_uploads_path', 'statamic/form-uploads').'/';
    }
}
