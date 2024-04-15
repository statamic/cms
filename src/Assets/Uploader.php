<?php

namespace Statamic\Assets;

use Facades\Statamic\Imaging\ImageValidator;
use Rhukster\DomSanitizer\DOMSanitizer;
use Statamic\Facades\Glide;
use Statamic\Support\Str;
use Symfony\Component\HttpFoundation\File\UploadedFile;

abstract class Uploader
{
    public function upload(UploadedFile $file)
    {
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
            return $cache.'/'.$server->makeImage($file->getFilename(), ['p' => $preset]);
        } catch (\Exception $exception) {
            return $file->getRealPath();
        }
    }

    private function write($sourcePath, $destinationPath)
    {
        $stream = fopen($sourcePath, 'r');

        if (config('statamic.assets.svg_sanitization_on_upload', true) && Str::endsWith($destinationPath, '.svg')) {
            $sanitizer = new DOMSanitizer(DOMSanitizer::SVG);
            $stream = $sanitizer->sanitize($svg = stream_get_contents($stream), [
                'remove-xml-tags' => ! Str::startsWith($svg, '<?xml'),
            ]);
        }

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
