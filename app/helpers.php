<?php

use Statamic\Facades\Path;

function cp_route($route, $params = [])
{
    if (! config('statamic.cp.enabled')) {
        return null;
    }

    $route = route('statamic.cp.' . $route, $params);

    // TODO: This is a temporary workaround to routes like
    // `route('assets.browse.edit', 'some/image.jpg')` outputting two slashes.
    // Can it be fixed with route regex, or is it a laravel bug?
    $route = preg_replace('/(?<!:)\/\//', '/', $route);

    return $route;
}

function statamic_path($path = null)
{
    return Path::tidy(__DIR__ . '/../' . $path);
}

if (! function_exists('user')) {
    function user()
    {
        return \Statamic\Facades\User::current();
    }
}

if (! function_exists('debugbar')) {
    function debugbar()
    {
        return optional();
    }
}

if (! function_exists('crumb')) {
    function crumb(...$values)
    {
        return implode(' ‹ ', array_map("__", $values));
    }
}
