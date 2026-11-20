<?php

namespace Statamic\Assets;

use Facades\Statamic\Imaging\ImageValidator;
use League\Flysystem\UnableToWriteFile;
use Statamic\Facades\Glide;
use Statamic\Support\Svg;
use Symfony\Component\HttpFoundation\File\UploadedFile;

abstract class Uploader
{
    /**
     * Whether the uploaded file was run through the source preset.
     */
    protected bool $processedSourceFile = false;

    public function upload(UploadedFile $file)
    {
        $this->processedSourceFile = false;

        $source = $this->processSourceFile($file);

        $this->write($source, $path = $this->uploadPath($file));

        if (app()->runningInConsole()) {
            app('files')->delete($source);

            return $path;
        }

        dispatch(fn () => app('files')->delete($source))->afterResponse();

        return $path;
    }

    private function processSourceFile(UploadedFile $file): string
    {
        if ($file->getMimeType() === 'image/gif') {
            return $file->getRealPath();
        }

        if (! $preset = $this->preset()) {
            return $file->getRealPath();
        }

        if (! ImageValidator::isValidImage($file->getClientOriginalExtension(), $file->getClientMimeType())) {
            return $file->getRealPath();
        }

        $server = Glide::server([
            'source' => $file->getPath(),
            'cache' => $cache = storage_path('statamic/glide/tmp'),
            'cache_with_file_extensions' => false,
        ]);

        try {
            $path = $cache.'/'.$server->makeImage($file->getFilename(), ['p' => $preset]);
        } catch (\Exception $exception) {
            return $file->getRealPath();
        }

        $this->processedSourceFile = true;

        return $path;
    }

    private function write($sourcePath, $destinationPath)
    {
        $stream = fopen($sourcePath, 'r');

        if (config('statamic.assets.svg_sanitization_on_upload', true) && trim(strtolower(pathinfo($destinationPath, PATHINFO_EXTENSION))) === 'svg') {
            $stream = Svg::sanitize(stream_get_contents($stream));
        }

        $path = $this->uploadPathPrefix().$destinationPath;
        $written = $this->disk()->put($path, $stream);

        if (is_resource($stream)) {
            fclose($stream);
        }

        if (! $written) {
            throw UnableToWriteFile::atLocation($path, 'Make sure the directory is writable.');
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
