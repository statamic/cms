<?php

namespace Statamic\Stache;

use DirectoryIterator;
use FilesystemIterator;
use Illuminate\Filesystem\Filesystem;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Statamic\Facades\Path;
use Symfony\Component\Finder\SplFileInfo;

class Traverser
{
    protected $filesystem;
    protected $filter;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    protected function getFiles($dir, $recursive)
    {
        $files = [];

        if ($recursive) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS | FilesystemIterator::CURRENT_AS_SELF),
                RecursiveIteratorIterator::CHILD_FIRST
            );
        } else {
            $iterator = new DirectoryIterator($dir);
        }

        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isDir() || $fileInfo->getFilename()[0] === '.') {
                continue;
            }

            $files[] = new SplFileInfo($fileInfo->getPathname(), $fileInfo->getPath(), $fileInfo->getFilename());
        }

        return $files;
    }

    public function traverse($store, $recursive = true)
    {
        if (! $dir = $store->directory()) {
            throw new \Exception("Store [{$store->key()}] does not have a directory defined.");
        }

        $dir = rtrim($dir, '/');

        if (! $this->filesystem->exists($dir)) {
            return collect();
        }

        $files = collect($this->getFiles($dir, $recursive));

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
