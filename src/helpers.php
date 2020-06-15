<?php

use Barryvdh\Debugbar\LaravelDebugbar;
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
        return app()->bound(LaravelDebugbar::class) ? app(LaravelDebugbar::class) : optional();
    }
}
