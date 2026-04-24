<?php

namespace Statamic\Http\Middleware;

use Closure;
use Statamic\Exceptions\ElevatedSessionAuthorizationException;

class RequireElevatedSession
{
    public function handle($request, Closure $next)
    {
        if (config('statamic.users.elevated_sessions_enabled') && ! $request->hasElevatedSession()) {
            throw new ElevatedSessionAuthorizationException;
        }

        return $next($request);
    }
}
