<?php

namespace Statamic\Http\Middleware\CP;

use Closure;

class RequireElevatedSession
{
    public function handle($request, Closure $next)
    {
        $isElevated = session()->get('statamic_elevated_until') > now()->timestamp;

        if (! $isElevated) {
            return response()->json(['error' => 'Requires an elevated session.'], 403);
        }

        return $next($request);
    }
}
