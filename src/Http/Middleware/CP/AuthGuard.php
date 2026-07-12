<?php

namespace Statamic\Http\Middleware\CP;

use Closure;
use Illuminate\Support\Facades\Auth;

class AuthGuard
{
    public function handle($request, Closure $next)
    {
        Auth::shouldUse(config('statamic.users.guards.cp', 'web'));

        return $next($request);
    }
}
