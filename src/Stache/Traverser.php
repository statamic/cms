<?php

namespace Statamic\Stache;

use Statamic\Facades\Path;
use Symfony\Component\Finder\Finder;

class Traverser
{
    protected $filter;

    public function traverse($store)
    {
        if (! $dir = $store->directory()) {
            throw new \Exception("Store [{$store->key()}] does not have a directory defined.");
        }

        $dir = rtrim($dir, '/');

        if (! file_exists($dir)) {
            return collect();
        }

        $files = Finder::create()->files()->ignoreDotFiles(true)->in($dir);

        $paths = [];
        foreach ($files as $file) {
            if ($this->filter && ! call_user_func($this->filter, $file)) {
                continue;
            }

            try {
                $mtime = $file->getMTime();
            } catch (\RuntimeException $e) {
                // Skip the file only when a fresh listing shows it was removed after it was listed
                $listing = @scandir($file->getPath());

                if ($listing === false || in_array($file->getFilename(), $listing, true)) {
                    throw $e;
                }

                continue;
            }

            $paths[Path::tidy($file->getPathname())] = $mtime;
        }

        // Sort by path, then (stably) by timestamp, so files with the same timestamp are
        // ordered by path. This is much faster than Finder's sortByName(), which calls
        // realpath() on both files for every comparison.
        ksort($paths, SORT_STRING);
        asort($paths);

        return collect($paths);
    }

    public function filter($filter)
    {
        $clone = clone $this;

        $clone->filter = $filter;

        return $clone;
    }
}
