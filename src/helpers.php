<?php

use Statamic\Facades\Path;

function cp_route($route, $params = [])
{
    return Statamic::cpRoute($route, $params);
}

function statamic_path($path = null)
{
    return Path::tidy(__DIR__ . '/../' . $path);
}

if (! function_exists('debugbar')) {
    function debugbar()
    {
        return optional();
    }
}
