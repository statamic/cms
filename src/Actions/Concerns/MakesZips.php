<?php

namespace Statamic\Actions\Concerns;

use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipStream\ZipStream;

trait MakesZips
{
    protected function makeZip($name, $files)
    {
        $zip = new ZipStream(
            outputName: $name,
            defaultEnableZeroHeader: true,
            sendHttpHeaders: true,
        );

        return tap($zip, function ($zip) use ($files) {
            $files->each(fn ($stream, $path) => $zip->addFileFromStream($path, $stream));
        });
    }

    protected function makeZipResponse($name, $files)
    {
        return new StreamedResponse(fn () => tap($this->makeZip($name, $files))->finish());
    }
}
