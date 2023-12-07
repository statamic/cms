<?php

namespace Statamic\Http\Controllers\CP\API;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Statamic\Http\Controllers\CP\CpController;

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
                        $views->push(str_replace_first($path.'/', '', str_before($file->getPathname(), '.')));
                    }
                }

                return $views->filter()->values();
            })
            ->values();
    }
}
