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

        // Use RecursiveDirectoryIterator for better performance
        // This is more memory efficient than allFiles() for large directories
        return $this->traverseWithIterator($dir, $store);
    }

    protected function traverseWithIterator($dir, $store)
    {
        try {
            $directoryIterator = new \RecursiveDirectoryIterator(
                $dir,
                \RecursiveDirectoryIterator::SKIP_DOTS
            );

            $filterIterator = new \RecursiveCallbackFilterIterator(
                $directoryIterator,
                function (\SplFileInfo $current, $key, \RecursiveDirectoryIterator $iterator) {
                    // Skip hidden files and directories
                    if (\str_starts_with($current->getFilename(), '.')) {
                        return false;
                    }

                    // Allow directories to be traversed
                    if ($current->isDir()) {
                        return true;
                    }

                    // For files, apply the custom filter if it exists
                    if ($this->filter) {
                        return call_user_func($this->filter, new \Symfony\Component\Finder\SplFileInfo(
                            $current->getPathname(),
                            $current->getPath(),
                            $current->getFilename()
                        ));
                    }

                    return true;
                }
            );

            $iterator = new \RecursiveIteratorIterator(
                $filterIterator,
                \RecursiveIteratorIterator::LEAVES_ONLY
            );

        } catch (\Exception $e) {
            return $this->traverseWithAllFiles($dir);
        }

        return collect(iterator_to_array($iterator))
            ->mapWithKeys(function ($file) {
                $path = Path::tidy($file->getPathname());

                return [$path => $file->getMTime()];
            })
            ->sort();
    }

    protected function traverseWithAllFiles($dir)
    {
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
