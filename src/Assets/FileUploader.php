<?php

namespace Statamic\Assets;

use Illuminate\Support\Facades\Storage;
use Statamic\Facades\AssetContainer;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader extends Uploader
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container ? AssetContainer::find($container) : null;
    }

    public static function container(string $container = null)
    {
        return new static($container);
    }

    protected function uploadPath(UploadedFile $file)
    {
        return now()->timestamp.'/'.$file->getClientOriginalName();
    }

    protected function uploadPathPrefix()
    {
        return 'statamic/file-uploads/';
    }

    protected function preset()
    {
        return optional($this->container)->sourcePreset();
    }

    protected function disk()
    {
        return Storage::disk('local');
    }
}
