<?php

namespace Statamic\Extend\Contextual;

use Statamic\API\Cache;

class ContextualCache extends ContextualObject
{
    /**
     * Save a key to the cache
     *
     * @param string $key   Key to save under
     * @param mixed  $data  Data to cache
     * @param int    $mins  Minutes to keep in the cache (defaults to null which means forever)
     */
    public function put($key, $data, $mins = null)
    {
        Cache::put($this->contextualize($key), $data, $mins);
    }

    /**
     * Get a key from the cache
     *
     * @param string $key      Key to retrieve
     * @param null   $default  Fallback data if the value doesn't exist
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return Cache::get($this->contextualize($key), $default);
    }

    /**
     * Remove a key from the cache
     *
     * @param string $key  Key to retrieve
     * @return bool
     */
    public function forget($key)
    {
        return Cache::forget($this->contextualize($key));
    }

    /**
     * Check if a key exists in the cache
     *
     * @param string $key  Key to check
     * @return bool
     */
    public function exists($key)
    {
        return (bool) $this->get($key);
    }
}
