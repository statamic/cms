<?php

namespace Statamic\Http\Middleware\CP;

use Closure;

class RequireElevatedSession
{
    public function handle($request, Closure $next)
    {
        if (! $request->hasElevatedSession()) {
            return response()->json(['error' => __('Requires an elevated session.')], 403);
        }

        return $next($request);
    }
}
