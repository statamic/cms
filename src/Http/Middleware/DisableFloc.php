<?php

namespace Statamic\Http\Middleware;

use Closure;

class DisableFloc
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        if (config('statamic.system.disable_floc', true)) {
            $response->headers->set('Permissions-Policy', 'interest-cohort=()');
        }

        return $response;
    }
}
