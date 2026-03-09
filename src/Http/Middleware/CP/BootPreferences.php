<?php

namespace Statamic\Http\Middleware\CP;

use Closure;
use Statamic\Facades\Preference;

class BootPreferences
{
    public function handle($request, Closure $next)
    {
        if (! $request->inertia() && $request->ajax()) {
            return $next($request);
        }

        Preference::boot();

        return $next($request);
    }
}
