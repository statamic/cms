<?php

namespace Statamic\Assets;

use Statamic\Facades\Glide;
use Symfony\Component\HttpFoundation\File\UploadedFile;

abstract class Uploader
{
    public function upload(UploadedFile $file)
    {
        $source = $this->processSourceFile($file);

        $this->write($source, $path = $this->uploadPath($file));

        app('files')->delete($source);

        return $path;
    }

    private function processSourceFile(UploadedFile $file): string
    {
        if (! $preset = $this->preset()) {
            return $file->getRealPath();
        }

        $server = Glide::server([
            'source' => $file->getPath(),
            'cache' => $cache = storage_path('statamic/glide/tmp'),
            'cache_with_file_extensions' => false,
        ]);

        try {
            return $cache.'/'.$server->makeImage($file->getFilename(), ['p' => $preset]);
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

    abstract protected function uploadPath(UploadedFile $file);

    protected function uploadPathPrefix()
    {
        return '';
    }

    abstract protected function preset();

    abstract protected function disk();
}
