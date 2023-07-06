<?php

namespace Statamic\Actions\Concerns;

use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipStream\Option\Archive;
use ZipStream\ZipStream;

trait MakesZips
{
    protected function makeZip($name, $files)
    {
        $options = new Archive;
        $options->setZeroHeader(true);
        $options->setSendHttpHeaders(true);

        return tap(new ZipStream($name, $options), function ($zip) use ($files) {
            $files->each(fn ($stream, $path) => $zip->addFileFromStream($path, $stream));
        });
    }

    protected function makeZipResponse($name, $files)
    {
        return new StreamedResponse(fn () => tap($this->makeZip($name, $files))->finish());
    }
}
