<?php

use Illuminate\Contracts\Container\BindingResolutionException;
use Statamic\Facades\Path;
use Statamic\Statamic;

function cp_route($route, $params = [])
{
    return Statamic::cpRoute($route, $params);
}

function statamic_path($path = null)
{
    return Path::tidy(__DIR__.'/../'.$path);
}

if (! function_exists('debugbar')) {
    function debugbar()
    {
        try {
            return app('debugbar');
        } catch (BindingResolutionException $e) {
            return optional();
        }
    }
}
