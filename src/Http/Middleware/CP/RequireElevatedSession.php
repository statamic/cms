<?php

namespace Statamic\Http\Middleware\CP;

use Closure;

class RequireElevatedSession
{
    public function handle($request, Closure $next)
    {
        $elevatedSessionIsActive = session()->get('statamic_elevated_until') > now()->timestamp;

        if (! $elevatedSessionIsActive) {
            return response()->json(['error' => __('Requires an elevated session.')], 403);
        }

        return $next($request);
    }
}
