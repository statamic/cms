<?php

namespace Statamic\Extensions;

use Illuminate\Cache\FileStore as LaravelFileStore;
use Illuminate\Contracts\Cache\Store;
use Statamic\Support\Str;

class FileStore extends LaravelFileStore implements Store
{
    private ?string $dir = null;

    public function path($key)
    {
        if (! Str::startsWith($key, 'stache::')) {
            return parent::path($key);
        }

        $key = Str::after($key, 'stache::');

        return $this->dir().str_replace('::', '/', $key);
    }

    private function dir()
    {
        if ($this->dir) {
            return $this->dir;
        }

        return $this->dir = Str::endsWith($this->directory, '/stache')
            ? $this->directory.'/'
            : $this->directory.'/stache/';
    }
}
