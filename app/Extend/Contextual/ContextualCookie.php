<?php

namespace Statamic\Extend\Contextual;

use Statamic\API\Cookie;

class ContextualCookie extends ContextualObject
{
    /**
     * Save a cookie
     *
     * @param string $key   Key to save under
     * @param mixed  $value Value to save
     * @param int    $mins  Minutes to keep the cookie (defaults to null which means forever / 5 years)
     */
    public function put($key, $value, $mins = null)
    {
        Cookie::put($this->contextualize($key), $value, $mins);
    }

    /**
     * Get a cookie
     *
     * @param string $key      Key to retrieve
     * @param null   $default  Fallback data if the cookie doesn't exist
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return Cookie::get($this->contextualize($key), $default);
    }

    /**
     * Remove a cookie
     *
     * @param string $key  Key to forget
     * @return bool
     */
    public function forget($key)
    {
        return Cookie::forget($this->contextualize($key));
    }

    /**
     * Does a cookie exists?
     *
     * @param string $key Key to retrieve
     * @return boolean
     */
    public function exists($key)
    {
        return Cookie::has($key);
    }
}
