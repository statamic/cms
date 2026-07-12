<?php

namespace Statamic\Http\Middleware\CP;

use Closure;
use Statamic\Facades\Preference;

class BootPreferences
{
    public function handle($request, Closure $next)
    {
        Preference::boot();

        return $next($request);
    }
}
