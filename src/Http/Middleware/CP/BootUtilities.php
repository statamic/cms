<?php

namespace Statamic\Http\Middleware\CP;

use Closure;
use Statamic\Facades\Utility;

class BootUtilities
{
    public function handle($request, Closure $next)
    {
        Utility::boot();

        return $next($request);
    }
}
