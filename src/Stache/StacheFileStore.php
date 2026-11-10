<?php

namespace Statamic\Stache;

use Illuminate\Cache\FileStore;

/**
 * A file cache store laid out for the Stache.
 *
 * Laravel's file store spreads keys across two levels of directories derived from
 * each key's hash, so building the Stache creates a new pair of directories for
 * almost every item. This store groups keys by their prefix instead, so all the
 * items in a Stache store share a single directory.
 */
class StacheFileStore extends FileStore
{
    public function path($key)
    {
        $prefix = str_contains($key, '::') ? substr($key, 0, strrpos($key, '::')) : '';

        // The filename is still a hash of the full key, so two prefixes that
        // happen to produce the same directory name can't collide.
        $directory = substr(trim(preg_replace('/[^A-Za-z0-9_-]+/', '.', $prefix), '.'), 0, 100) ?: '_';

        return $this->directory.'/'.$directory.'/'.sha1($key);
    }
}
