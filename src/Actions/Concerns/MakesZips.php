<?php

namespace Statamic\Actions\Concerns;

use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipStream\Option\Archive;
use ZipStream\ZipStream;

trait MakesZips
{
    protected function makeZip($name, $files)
    {
        return new StreamedResponse(function () use ($name, $files) {
            $options = new Archive();
            $options->setZeroHeader(true);
            $options->setSendHttpHeaders(true);

            $zip = new ZipStream($name, $options);
            $files->each(function ($stream, $basename) use ($zip) {
                $zip->addFileFromStream($basename, $stream);
            });

            $zip->finish();
        });
    }
}
