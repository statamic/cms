<?php

namespace Statamic\API;

class Cookie
{
    /**
     * @return \Illuminate\Cookie\CookieJar
     */
    private static function cookie()
    {
        return cookie();
    }

    /**
     * Save a cookie
     *
     * @param string $key   Key to save under
     * @param mixed  $value Value to save
     * @param int    $mins  Minutes to keep the cookie (defaults to null which means forever / 5 years)
     */
    public static function put($key, $value, $mins = null)
    {
        $cookie = (is_null($mins))
            ? self::cookie()->forever($key, $value)
            : self::cookie()->make($key, $value, $mins);

        self::cookie()->queue($cookie);
    }

    /**
     * Get a cookie
     *
     * @param string $key      Key to retrieve
     * @param null   $default  Fallback data if the cookie doesn't exist
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        return request()->cookie($key, $default);
    }

    /**
     * Does a cookie exists?
     *
     * @param string $key Key to retrieve
     * @return boolean
     */
    public static function has($key)
    {
        return ! is_null(self::get($key));
    }

    /**
     * Remove a cookie
     *
     * @param string $key  Key to forget
     * @return bool
     */
    public static function forget($key)
    {
        return self::cookie()->forget($key);
    }
}
