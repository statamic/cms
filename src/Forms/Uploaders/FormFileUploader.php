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
        return "{$this->submissionId}/{$this->handle}/{$file->getClientOriginalName()}";
    }

    protected function uploadPathPrefix()
    {
        return 'statamic/form-uploads/';
    }
}
