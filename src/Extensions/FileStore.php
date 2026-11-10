<?php

namespace Statamic\Extensions;

use Illuminate\Cache\FileStore as LaravelFileStore;
use Illuminate\Contracts\Cache\Store;
use Statamic\Support\Str;
use Throwable;

class FileStore extends LaravelFileStore implements Store
{
    /**
     * The expiration Laravel writes for items stored forever.
     */
    private const FOREVER = '9999999999';

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

    /**
     * Retrieve an item from the cache by key.
     *
     * Behaves like the parent, but the Stache reads far more often than it writes and
     * almost everything it stores never expires. So this opens the file directly rather
     * than checking it exists first, and only checks the clock for items that can expire.
     */
    public function get($key)
    {
        if (! $handle = @fopen($this->path($key), 'rb')) {
            return null;
        }

        try {
            // Like the parent, take a shared lock so we never read a file mid-write.
            if (! flock($handle, LOCK_SH)) {
                return null;
            }

            $contents = stream_get_contents($handle);

            flock($handle, LOCK_UN);
        } finally {
            fclose($handle);
        }

        $expire = substr($contents, 0, 10);

        if ($expire !== self::FOREVER && $this->currentTime() >= (int) $expire) {
            $this->forget($key);

            return null;
        }

        try {
            return $this->unserializeValue(substr($contents, 10));
        } catch (Throwable) {
            $this->forget($key);

            return null;
        }
    }

    private function unserializeValue(string $value)
    {
        // Laravel only supports restricting unserializable classes in newer versions.
        if (method_exists(LaravelFileStore::class, 'unserialize')) {
            return $this->unserialize($value);
        }

        return unserialize($value);
    }
}
