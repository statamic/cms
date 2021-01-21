<?php

namespace Statamic\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class UserGuards
{
    public function handle($request, Closure $next)
    {
        Auth::shouldUse(config('statamic.users.guard', 'web'));

        return $next($request);
    }
}
