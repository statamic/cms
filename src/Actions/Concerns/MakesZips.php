<?php

namespace Statamic\Actions\Concerns;

use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipStream\Option\Archive;
use ZipStream\ZipStream;

trait MakesZips
{
    protected function makeZip($name, $files)
    {
        $options = new Archive();
        $options->setZeroHeader(true);
        $options->setSendHttpHeaders(true);

        $zip = new ZipStream($name, $options);
        $files->each(function ($stream, $path) use ($zip) {
            $zip->addFileFromStream($path, $stream);
        });

        return $zip;
    }

    protected function makeZipResponse($name, $files)
    {
        return new StreamedResponse(fn () => tap($this->makeZip($name, $files))->finish());
    }
}
