<?php

namespace Statamic\API;

class Cache
{
    /**
     * Get the cache instance
     *
     * @return  \Illuminate\Contracts\Cache\Store
     */
    public static function cache()
    {
        return app('cache');
    }

    /**
     * Save a key to the cache
     *
     * @param string $key   Key to save under
     * @param mixed  $data  Data to cache
     * @param int    $mins  Minutes to keep in the cache (defaults to null which means forever)
     */
    public static function put($key, $data, $mins = null)
    {
        if (is_null($mins)) {
            self::cache()->forever($key, $data);
        } else {
            self::cache()->put($key, $data, $mins);
        }
    }

    /**
     * Get a key from the cache
     *
     * @param string $key      Key to retrieve
     * @param null   $default  Fallback data if the value doesn't exist
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        return self::cache()->get($key, $default);
    }

    /**
     * Does a key exist in the cache?
     *
     * @param string $key      Key to retrieve
     * @return boolean
     */
    public static function has($key)
    {
        return self::cache()->has($key);
    }

    /**
     * Remove a key from the cache
     *
     * @param string $key  Key to forget
     * @return bool
     */
    public static function forget($key)
    {
        return self::cache()->forget($key);
    }

    /**
     * Clear the entire cache
     */
    public static function clear()
    {
        \Artisan::call('cache:clear');
    }
}
