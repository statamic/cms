<?php

namespace Statamic\Http\Middleware\CP;

use Closure;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Statamic\Exceptions\ControlPanelExceptionHandler;

class SwapExceptionHandler
{
    public function handle($request, Closure $next)
    {
        app()->singleton(ExceptionHandler::class, ControlPanelExceptionHandler::class);

        return $next($request);
    }
}
