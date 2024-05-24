<?php

namespace Statamic\Http\Controllers\CP\API;

use RecursiveCallbackFilterIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Support\Str;

class TemplatesController extends CpController
{
    public function index()
    {
        return collect(config('view.paths'))->flatMap(function ($path) {
            return collect(new RecursiveIteratorIterator(
                new RecursiveCallbackFilterIterator(
                    new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS | RecursiveDirectoryIterator::FOLLOW_SYMLINKS),
                    fn ($file) => ! str_starts_with($file->getFilename(), '.') && ! in_array($file->getBaseName(), ['node_modules'])
                )
            ))->map(fn ($file) => Str::of($file->getPathname())
                ->after($path.DIRECTORY_SEPARATOR)
                ->before('.')
                ->replace('\\', '/')
            )->sort()->values();
        });
    }
}
