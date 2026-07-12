<?php

namespace Statamic\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AuthGuard
{
    public function handle($request, Closure $next)
    {
        Auth::shouldUse(config('statamic.users.guards.web', 'web'));

        return $next($request);
    }
}
