<?php

namespace Statamic\Stache;

use Illuminate\Filesystem\Filesystem;
use Statamic\Facades\Path;

class Traverser
{
    protected $filesystem;
    protected $filter;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function traverse($store)
    {
        if (! $dir = $store->directory()) {
            throw new \Exception("Store [{$store->key()}] does not have a directory defined.");
        }

        $dir = rtrim($dir, '/');

        if (! $this->filesystem->exists($dir)) {
            return collect();
        }

        $files = collect($this->filesystem->allFiles($dir));

        if ($this->filter) {
            $files = $files->filter($this->filter);
        }

        return $files
            ->mapWithKeys(function ($file) {
                return [Path::tidy($file->getPathname()) => $file->getMTime()];
            })->sort();
    }

    public function filter($filter)
    {
        $this->filter = $filter;

        return $this;
    }
}
