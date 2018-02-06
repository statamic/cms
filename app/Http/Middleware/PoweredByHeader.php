<?php

namespace Statamic\Http\Middleware;

use Closure;

class PoweredByHeader
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

        if (config('statamic.system.send_powered_by_header')) {
            $response->header('X-Powered-By', 'Statamic');
        }

        return $response;
    }
}
