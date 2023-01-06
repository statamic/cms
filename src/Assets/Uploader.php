<?php

namespace Statamic\Assets;

use Illuminate\Filesystem\Filesystem;
use Statamic\Facades\Glide;
use Symfony\Component\HttpFoundation\File\UploadedFile;

abstract class Uploader
{
    private $files;
    private $glideTmpPath;

    public function __construct()
    {
        $this->files = app(Filesystem::class);
        $this->glideTmpPath = storage_path('statamic/glide/tmp');
    }

    public function upload(UploadedFile $file)
    {
        $source = $this->processSourceFile($file);

        $this->write($source, $path = $this->uploadPath($file));

        $this->deleteTemporaryFiles();

        return $path;
    }

    private function processSourceFile(UploadedFile $file): string
    {
        if (! $preset = $this->preset()) {
            return $file->getRealPath();
        }

        $server = Glide::server([
            'source' => $file->getPath(),
            'cache' => $this->glideTmpPath,
            'cache_with_file_extensions' => false,
        ]);

        try {
            return $this->glideTmpPath.'/'.$server->makeImage($file->getFilename(), ['p' => $preset]);
        } catch (\Exception $exception) {
            // Glide can't process the file, ie. it's not an image.
            return $file->getRealPath();
        }
    }

    private function write($sourcePath, $destinationPath)
    {
        $stream = fopen($sourcePath, 'r');

        $this->disk()->put($this->uploadPathPrefix().$destinationPath, $stream);

        if (is_resource($stream)) {
            fclose($stream);
        }
    }

    private function deleteTemporaryFiles()
    {
        if ($this->files->exists($this->glideTmpPath)) {
            $this->files->deleteDirectory($this->glideTmpPath);
        }
    }

    abstract protected function uploadPath(UploadedFile $file);

    protected function uploadPathPrefix()
    {
        return '';
    }

    abstract protected function preset();

    abstract protected function disk();
}
