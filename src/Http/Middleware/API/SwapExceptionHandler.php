<?php

namespace Statamic\Http\Middleware\API;

use Closure;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Statamic\Exceptions\ApiExceptionHandler;

class SwapExceptionHandler
{
    public function handle($request, Closure $next)
    {
        app()->singleton(ExceptionHandler::class, ApiExceptionHandler::class);

        return $next($request);
    }
}
