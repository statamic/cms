<?php

namespace Statamic\Http\Middleware\CP;

use Closure;
use Statamic\Facades\Utility;

class BootUtilities
{
    public function handle($request, Closure $next)
    {
        if (! $request->inertia() && $request->ajax()) {
            return $next($request);
        }

        Utility::boot();

        return $next($request);
    }
}
