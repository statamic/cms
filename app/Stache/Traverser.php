<?php

namespace Statamic\Stache;

use Illuminate\Filesystem\Filesystem;

class Traverser
{
    protected $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function traverse($store)
    {
        if (! $dir = $store->directory()) {
            throw new \Exception("Store [{$store->key()}] does not have a directory defined.");
        }

        return collect($this->filesystem->allFiles($dir))
            ->filter(function ($item) use ($store) {
                return $store->filter($item);
            })
            ->mapWithKeys(function ($file) {
                return [$file->getPathname() => $file->getMTime()];
            });
    }
}
