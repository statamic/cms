<?php

namespace Statamic\Http\Middleware;

use Closure;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Statamic\Support\Str;

abstract class SwapExceptionHandler
{
    abstract protected function handler();

    public function handle($request, Closure $next)
    {
        if (! $this->hasDisabledExceptionHandlingInTests()) {
            app()->singleton(ExceptionHandler::class, $this->handler());
        }

        return $next($request);
    }

    protected function hasDisabledExceptionHandlingInTests()
    {
        $class = get_class(app(ExceptionHandler::class));

        return Str::contains($class, 'InteractsWithExceptionHandling');
    }
}
