<?php

namespace Statamic\Http\Controllers\CP\API;

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
                $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));

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
