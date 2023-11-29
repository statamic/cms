<?php

namespace Statamic\View;

use Illuminate\Support\Collection;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class Views
{
    public static function all(): Collection
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
