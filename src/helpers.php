<?php

use Barryvdh\Debugbar\LaravelDebugbar;
use Statamic\Extend\Addon;
use Statamic\Facades\Addon as AddonFacade;
use Statamic\Facades\Path;
use Statamic\Statamic;

function addon(): ?Addon
{
    $callingClass = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['class'];

    $reflection = new ReflectionClass($callingClass);

    return AddonFacade::getByNamespace($reflection->getNamespaceName());
}

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
