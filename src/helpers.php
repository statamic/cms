<?php

use Barryvdh\Debugbar\LaravelDebugbar;
use Statamic\Facades\Path;
use Statamic\Statamic;
use Statamic\Support\Str;
use Statamic\Tags\Loader as TagLoader;
use Statamic\View\Antlers\Parser;

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

function tag(string $name, array $params = [])
{
    if ($pos = strpos($name, ':')) {
        $original_method = substr($name, $pos + 1);
        $method = Str::camel($original_method);
        $name = substr($name, 0, $pos);
    } else {
        $method = $original_method = 'index';
    }

    $tag = app(TagLoader::class)->load($name, [
        'parser'     => app(Parser::class),
        'params'     => $params,
        'content'    => '',
        'context'    => [],
        'tag'        => $name.':'.$original_method,
        'tag_method' => $original_method,
    ]);

    return call_user_func([$tag, $method]);
}
