<?php

namespace Statamic\Http\Controllers\CP\API;

use FilesystemIterator;
use RecursiveCallbackFilterIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Support\Str;

class TemplatesController extends CpController
{
    public function index()
    {
        return collect(config('view.paths'))
            ->flatMap(function ($path) {
                $views = collect();
                $filter = ['.git', 'node_modules'];

                $iterator = new RecursiveIteratorIterator(
                    new RecursiveCallbackFilterIterator(
                        new RecursiveDirectoryIterator(
                            $path,
                            FilesystemIterator::SKIP_DOTS
                        ),
                        function ($fileInfo, $key, $iterator) use ($filter) {
                            return $fileInfo->isFile() || ! in_array($fileInfo->getBaseName(), $filter);
                        }
                    )
                );

                foreach ($iterator as $file) {
                    if ($file->isFile()) {
                        $viewPath = Str::of($file->getPathname())
                            ->after($path.DIRECTORY_SEPARATOR)
                            ->before('.')
                            ->replace('\\', '/')
                            ->toString();

                        $views->push($viewPath);
                    }
                }

                return $views->filter()->sort()->values();
            })
            ->values();
    }
}
