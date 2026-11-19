<?php

namespace Statamic\API\Middleware;

use Closure;
use Statamic\Exceptions\ApiAuthenticationException;

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
            throw new ApiAuthenticationException;
        }

        return $next($request);
    }
}
