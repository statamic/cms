<?php

namespace Statamic\API;

use Statamic\API\Str;

class Request
{
    public static function all()
    {
        return request()->all();
    }

    public static function has($key)
    {
        return request()->has($key);
    }

    public static function get($key, $default = null)
    {
        return request()->get($key, $default);
    }

    public static function input($key, $default = null)
    {
        return request()->input($key, $default);
    }

    public static function query($key, $default = null)
    {
        return request()->query($key, $default);
    }

    public static function only($keys)
    {
        return request()->only($keys);
    }

    public static function except($keys)
    {
        return request()->except($keys);
    }

    public static function create($uri, $method = 'GET', $parameters = [])
    {
        return request()->create($uri, $method, $parameters);
    }

    public static function isCp()
    {
        return Str::startsWith(request()->path(), CP_ROUTE);
    }
}
