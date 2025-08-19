<?php

namespace Statamic\API\Middleware;

use Closure;
use Statamic\Exceptions\AuthenticationException;

class Authentication
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($token = config('statamic.api.api_token')) {
            if ($token !== $request->bearerToken()) {
                throw new AuthenticationException();
            }
        }

        return $next($request);
    }
}
