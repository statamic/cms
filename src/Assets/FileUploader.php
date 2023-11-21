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
        $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        return now()->timestamp.'/'.$filename.'.'.$this->extension($file);
    }

    private function extension(UploadedFile $file)
    {
        $extension = $file->getClientOriginalExtension();
        $guessed = $file->guessExtension();

        $isEqual = $extension === $guessed
            || ($extension === 'jpeg' && $guessed === 'jpg')
            || ($extension === 'jpg' && $guessed === 'jpeg');

        return $isEqual ? $extension : $guessed;
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
