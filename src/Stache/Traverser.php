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

        $files = Finder::create()->files()->ignoreDotFiles(true)->in($dir)->sortByName();

        $paths = [];
        foreach ($files as $file) {
            if ($this->filter && ! call_user_func($this->filter, $file)) {
                continue;
            }

            $paths[Path::tidy($file->getPathname())] = $file->getMTime();
        }

        return collect($paths)->sort();
    }

    public function filter($filter)
    {
        $this->filter = $filter;

        return $this;
    }
}
