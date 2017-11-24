<?php

namespace Statamic\Extensions;

use Illuminate\Contracts\Cache\Store;
use Illuminate\Cache\FileStore as LaravelFileStore;

class FileStore extends LaravelFileStore implements Store
{
    /**
     * Get the full path for the given cache key.
     *
     * @param  string  $key
     * @return string
     */
    protected function path($key)
    {
        $namespaces = explode(':', $key);
        array_pop($namespaces);

        // Stache keys get put into their own folder for readability.
        if (isset($namespaces[0]) && $namespaces[0] === 'stache') {
            return $this->getStachePath($key);
        }

        $parts = array_slice(str_split($hash = md5($key), 2), 0, 2);

        return $this->directory.'/'.implode('/', $namespaces).'/'.implode('/', $parts).'/'.$hash;
    }

    /**
     * Stache keys will get stored without being hashed
     *
     * @param string $key
     * @return string
     */
    private function getStachePath($key)
    {
        // remove the "stache::" prefix
        $key = substr($key, 8);

        return $this->directory.'/stache/'.str_replace('::', '/', $key);
    }
}
