<?php

namespace Statamic\API\Middleware;

use Closure;

class HandleAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (
            ($token = config('statamic.api.auth_token'))
            && ($request->bearerToken() !== $token)
        ) {
            abort(401);
        }

        return $next($request);
    }
}
