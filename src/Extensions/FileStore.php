<?php

namespace Statamic\Extensions;

use Illuminate\Cache\FileStore as LaravelFileStore;
use Illuminate\Contracts\Cache\Store;
use Statamic\Support\Str;

class FileStore extends LaravelFileStore implements Store
{
    protected function path($key)
    {
        if (! Str::startsWith($key, 'stache::')) {
            return parent::path($key);
        }

        $key = Str::after($key, 'stache::');

        return $this->directory.'/stache/'.str_replace('::', '/', $key);
    }
}
