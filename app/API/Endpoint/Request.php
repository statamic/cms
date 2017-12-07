<?php

namespace Statamic\API\Endpoint;

use Statamic\API\Str;

class Request
{
    public function all()
    {
        return request()->all();
    }

    public function has($key)
    {
        return request()->has($key);
    }

    public function get($key, $default = null)
    {
        return request()->get($key, $default);
    }

    public function input($key, $default = null)
    {
        return request()->input($key, $default);
    }

    public function query($key, $default = null)
    {
        return request()->query($key, $default);
    }

    public function only($keys)
    {
        return request()->only($keys);
    }

    public function except($keys)
    {
        return request()->except($keys);
    }

    public function create($uri, $method = 'GET', $parameters = [])
    {
        return request()->create($uri, $method, $parameters);
    }

    public function isCp()
    {
        return Str::startsWith(request()->path(), CP_ROUTE);
    }
}
