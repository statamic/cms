<?php

namespace Statamic\Forms\Uploaders;

use Statamic\Assets\FileUploader;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @phpstan-consistent-constructor
 */
class FormFileUploader extends FileUploader
{
    public function __construct(protected string $submissionId, protected string $handle, ?string $container = null)
    {
        parent::__construct($container);
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
