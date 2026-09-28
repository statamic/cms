<?php

namespace Statamic\Http\Middleware;

use Closure;
use Statamic\Statamic;

class SnapshotJsonVariables
{
    /**
     * In long-lived processes, Statamic::$jsonVariables outlives the request, so
     * per-request additions (CSRF token, current user, permissions) would leak
     * into later requests. To prevent that, we snapshot the boot-time
     * registrations so we can revert to them after each request, wiping out
     * anything that was added while handling it.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        Statamic::snapshotJsonVariables();

        return $next($request);
    }
}
