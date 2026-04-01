<?php

namespace Statamic\Http\Middleware;

use Closure;
use Statamic\Exceptions\ElevatedSessionAuthorizationException;

class RequireElevatedSession
{
    public function handle($request, Closure $next)
    {
        if (! $request->hasElevatedSession()) {
            throw new ElevatedSessionAuthorizationException;
        }

        return $next($request);
    }
}
